<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Keepers\KeeperCardFactory;
use StarFluxx\Cards\Goals\GoalCardFactory;
use StarFluxx\Cards\Rules\RuleCardFactory;
use StarFluxx\Cards\Actions\ActionCardFactory;
use StarFluxx\Cards\Creepers\CreeperCardFactory;
use starfluxx;

trait PlayCardTrait
{
  public function st_playCard()
  {
    $game = Utils::getGame();
    $player_id = $game->getActivePlayerId();

    // If any card is still in "pending" play for others to Surprise on,
    // play it now if nobody Surprise countered, or let Surprise counter it
    if ($this->checkSurpriseCounterPlay())
    {      
      return;
    }

    // If any card is a force move, play it
    $forcedCardId = $game->getGameStateValue("forcedCard");

    if ($forcedCardId != -1) {
      $game->setGameStateValue("forcedCard", -1);
      // But forced play cards should not really be counted for play rule
      self::action_forced_playCard($forcedCardId);
      return;
    }

    // If there are still cards left in Temp Hands but no more forced plays,
    // discard the leftovers from the Temp Hands
    $tmpHandActive = $this->checkTempHandsForDiscard($player_id);
    // If we still have Temp Hands active with more cards to play, do this first
    if ($tmpHandActive > 0) {
      $game->gamestate->nextstate("resolveTempHand");
      return;
    }

    $forceRecheck = $game->getGameStateValue("creeperForcedRecheckNeeded");
    $lastCardPlayed = null;
    if ($forceRecheck) {
      $lastCardPlayed = ["type" => "goal", "id" => 0, "type_arg" => 0];
    }    
    // check if any creepers should be checked again because of the cards just drawn    
    if ($game->checkCreeperResolveNeeded($lastCardPlayed)) {
      return;
    }

    // before we play anything new, check again if we have a winner at this point
    if ($this->checkWinConditions()) {
      return;
    }

    // If anything happened during previous plays that forces this player to end turn immediately
    $forcedTurnEnd = $game->getGameStateValue("forcedTurnEnd");
    if ($forcedTurnEnd > 0) {
      $game->gamestate->nextstate("endOfTurn");
      return;
    }

    // If any "free action" rule can be played, we cannot end turn automatically
    // Player must finish its turn by explicitly deciding not to use any of the free rules
    $freeRulesAvailable = $this->getFreeRulesAvailable($player_id);
    if (count($freeRulesAvailable) > 0) {
      return;
    }

    if (!$this->activePlayerMustPlayMoreCards($player_id)) {
      $game->gamestate->nextstate("endOfTurn");
    }
  }

  private function checkSurpriseCounterPlay() 
  {
    $game = Utils::getGame();
    $surpriseTargetId = $game->getGameStateValue("cardIdSurpriseTarget");

    if ($surpriseTargetId == -1)
      return false;

    $surpriseCounterId = $game->getGameStateValue("cardIdSurpriseCounter");

    // Surprise countered the card played
    if ($surpriseCounterId != -1) {

      $targetCard = $game->cards->getCard($surpriseTargetId);
      $surpriseCard = $game->cards->getCard($surpriseCounterId);
      $surpriseCardDef = $game->getCardDefinitionFor($surpriseCard);
      $surprisePlayerId = $surpriseCard["location_arg"];

      $surpriseCardDef->outOfTurnCounterPlay($surpriseTargetId);

      $players = $game->loadPlayersBasicInfos();
      $game->notifyAllPlayers("surprise", 
        clienttranslate('${player_name} uses <b>${card_surprise}</b> as surprise against <b>${card_target}</b>'),
        [
          "i18n" => ["card_target", "card_surprise"],
          "player_name" => $players[$surprisePlayerId]["player_name"],
          "card_surprise" => $game->getCardDefinitionFor($surpriseCard)->getName(),
          "card_target" => $game->getCardDefinitionFor($targetCard)->getName(),
        ]);

      // the Surprised card does still count as played
      $game->incGameStateValue("playedCards", 1);
      // and we should force refresh args for PlayCard state
      $game->gamestate->nextstate("continuePlay"); // force arg refresh
    }
    // allowed to play, just do it again from active player hand
    else {
      $player_id = $game->getActivePlayerId();
      self::_action_playCard($surpriseTargetId, $player_id, true);
    }

    $game->setGameStateValue("cardIdSurpriseTarget", -1);
    $game->setGameStateValue("cardIdSurpriseCounter", -1);

    return true;  

  }

  private function checkTempHandsForDiscard($player_id)
  {
    $game = Utils::getGame();
    $tmpHandActive = Utils::getActiveTempHandWithPlays();
    for ($i = 3; $i >= 1; $i--) {
      $tmpHandLocation = "tmpHand" . $i;
      $tmpHandCard = $game->getGameStateValue($tmpHandLocation . "Card");
      // there was a Temp Hand active above the current one:
      // reset it and check for remaining tmp cards to discard
      if ($tmpHandCard > 0 && $i > $tmpHandActive) {
        $game->setGameStateValue($tmpHandLocation . "Card", -1);
        $cardsToDiscard = $game->cards->getCardsInLocation(
          $tmpHandLocation,
          $player_id
        );

        // discard all remaining cards
        foreach ($cardsToDiscard as $card_id => $card) {
          $game->cards->playCard($card_id);
        }
        if (count($cardsToDiscard) > 0) {
          $game->notifyAllPlayers("tmpHandDiscarded", "", [
            "tmpHand" => $i,
            "player_id" => $player_id,
            "cards" => $cardsToDiscard,
            "discardCount" => $game->cards->countCardInLocation("discard"),
          ]);
        }
      }
    }
    return $tmpHandActive;
  }

  private function activePlayerMustPlayMoreCards($player_id)
  {
    $leftToPlay = Utils::calculateCardsLeftToPlayFor($player_id);

    return $leftToPlay > 0;
  }

  public function arg_playCard()
  {
    $game = Utils::getGame();
    $player_id = $game->getActivePlayerId();

    $alreadyPlayed = $game->getGameStateValue("playedCards");
    $mustPlay = Utils::calculateCardsMustPlayFor($player_id, true);

    $leftToPlay = Utils::calculateCardsLeftToPlayFor($player_id);

    $countLabelText = "";
    $countLabelNr = "";
    if ($mustPlay >= PLAY_COUNT_ALL) {
      $countLabelText = clienttranslate("All");
      $countLabelNr = "";
    } elseif ($mustPlay < 0) {
      $countLabelText = clienttranslate("All but");
      $countLabelNr = " " . -$mustPlay;
    } else {
      $countLabelText = "";
      $countLabelNr = $leftToPlay;
    }

    $freeRulesAvailable = $this->getFreeRulesAvailable($player_id);

    return [      
      "i18n" => ["countLabelText"],
      "countLabelText" => $countLabelText,
      "countLabelNr" => $countLabelNr,
      "count" => $leftToPlay,
      "freeRules" => $freeRulesAvailable,
    ];
  }

  private function getFreeRulesAvailable($player_id)
  {
    $freeRulesAvailable = [];

    $game = Utils::getGame();
    $rulesInPlay = $game->cards->getCardsInLocation("rules", RULE_OTHERS);
    foreach ($rulesInPlay as $card_id => $rule) {
      $ruleCard = RuleCardFactory::getCard($rule["id"], $rule["type_arg"]);

      if ($ruleCard->canBeUsedInPlayerTurn($player_id)) {
        $freeRulesAvailable[] = [
          "card_id" => $card_id,
          "name" => $ruleCard->getName(),
        ];
      }
    }
    // also check Keepers with special abilities
    $keepersInPlay = $game->cards->getCardsInLocation("keepers", $player_id);
    foreach ($keepersInPlay as $card_id => $keeper) {
      if ($keeper["type_arg"] > 50) continue; // skip creepers
      $keeperCard = KeeperCardFactory::getCard($keeper["id"], $keeper["type_arg"]);

      if ($keeperCard->canBeUsedInPlayerTurn($player_id)) {
        $freeRulesAvailable[] = [
          "card_id" => $card_id,
          "name" => $keeperCard->getName(),
        ];
      }
    }

    return $freeRulesAvailable;
  }

  public function action_finishTurn()
  {
    $game = Utils::getGame();
    // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
    $game->checkAction("finishTurn");

    $player_id = $game->getActivePlayerId();
    if ($this->activePlayerMustPlayMoreCards($player_id)) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You cannot finish your turn if you still need to play cards"
        )
      );
    }

    $game->gamestate->nextstate("endOfTurn");
  }

  public function action_playFreeRule($card_id)
  {
    $game = Utils::getGame();

    // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
    $game->checkAction("playFreeRule");

    $player_id = $game->getActivePlayerId();
    $card = $game->cards->getCard($card_id);

    $freePlayCard = null;
    if ($card["type"] == "rule") {
      if ($card["location"] != "rules") {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate("This is not an active Rule")
        );
      }
  
      $freePlayCard = RuleCardFactory::getCard($card_id, $card["type_arg"]);
    } 
    else if ($card["type"] == "keeper") {
      if ($card["location"] != "keepers" || $card["location_arg"] != $player_id) {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate("This is not one of your Keepers")
        );
      }
  
      $freePlayCard = KeeperCardFactory::getCard($card_id, $card["type_arg"]);
    }

    $game->notifyAllPlayers(
      "freeRulePlayed",
      clienttranslate('${player_name} uses free play <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $freePlayCard->getName(),
      ]
    );

    $stateTransition = $freePlayCard->freePlayInPlayerTurn($player_id);
    if ($stateTransition != null) {
      // player must resolve something before continuing to play more cards
      $game->gamestate->nextstate($stateTransition);
    } else {
      // else: just let player continue playing cards
      // but explicitly set state again to force args refresh
      $game->gamestate->nextstate("continuePlay");
    }
  }

  public function action_playCard($card_id, $postponeCreeperResolve = false)
  {
    // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
    $game = Utils::getGame();
    $game->checkAction("playCard");

    // and Check that the active player is actually allowed to play more cards!
    // (maybe turn is still active only because they have free rules left to play)
    $player_id = $game->getActivePlayerId();
    if (!$this->activePlayerMustPlayMoreCards($player_id)) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate("You don't have any card plays left")
      );
    }

    // play the card from active player's hand
    $player_id = $game->getActivePlayerId();
    self::_action_playCard($card_id, $player_id, true, $postponeCreeperResolve);
  }

  public function action_forced_playCard($card_id)
  {
    // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
    $game = Utils::getGame();
    $game->checkAction("playCard");
    // play the card from active player's hand, but don't count it for nr played cards
    $player_id = $game->getActivePlayerId();
    self::_action_playCard($card_id, $player_id, false);
  }

  public function action_forced_playCard_forOtherPlayer($card_id, $player_id)
  {
    // immediately play card without "checkAction": can be in any state here
    // and don't add this to "play count", and postpone resolving the creeper
    self::_action_playCard($card_id, $player_id, false, true);
  }

  private function _action_playCard_checkForSurprises($player_id, $card)
  {
    $card_id = $card["id"];
    $alreadyChecked = self::getGameStateValue("cardIdSurpriseTarget");
    if ($alreadyChecked == $card_id) {
      return null;
    }

    $card_type = $card["type"];
    $surprise = null;
    switch ($card_type) {
      case "keeper":
        // That's Mine = 318
        $surprise = Utils::findPlayerWithSurpriseInHand(318);
        break;
      case "goal":
        // Canceled Plans = 321
        $surprise = Utils::findPlayerWithSurpriseInHand(321);
        break;
      case "rule":
        // Veto = 319
        $surprise = Utils::findPlayerWithSurpriseInHand(319);
        break;
      case "action":
        // BelayThat = 320
        $surprise = Utils::findPlayerWithSurpriseInHand(320);
        break;
      default:
        break;
    }

    self::dump("===SURPRISE-CHK===", [
      "target" => $card,
      "surprise" => $surprise,
    ]);

    if ($surprise != null && $surprise["player_id"] != $player_id) {
      self::setGameStateValue("cardIdSurpriseTarget", $card_id);
      return "checkForSurprises";
      }

    return null;
  }

  private function _action_playCard(
    $card_id,
    $player_id,
    $incrementPlayedCards,
    $postponeCreeperResolve = false
  ) {
    $game = Utils::getGame();

    $card = $game->cards->getCard($card_id);

    if ($card["location"] != "hand" or $card["location_arg"] != $player_id) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate("You do not have this card in hand")
      );
    }
    
    // check if some other players have Surprise actions that could prevent this play
    // If so, ask all players (multi) if they want to play a Surprise 
    // (prevent exposing which player actually has as Surprise)
    // = state "checkForSurprises"
    // If no Surprise, only then let the play go through
    // If Surprised, allow more Surprise-cancelling-Surprise until decided if the card can be played
    $stateTransition = self::_action_playCard_checkForSurprises($player_id, $card);
    if ($stateTransition != null) {
      $game->gamestate->nextstate($stateTransition);
      return;
    }

    // @TODO: It's a Trap is special: this should also be checked again after resolving Actions,
    // and after resolving any Free Rule/Keeper plays that might steal keepers.

    $card_type = $card["type"];
    $stateTransition = null;
    $continuePlayTransition = "continuePlay";
    switch ($card_type) {
      case "keeper":
        $this->playKeeperCard($player_id, $card);
        break;
      case "goal":
        $stateTransition = $this->playGoalCard($player_id, $card);
        break;
      case "rule":
        $stateTransition = $this->playRuleCard($player_id, $card);
        break;
      case "action":
        $stateTransition = $this->playActionCard($player_id, $card);
        break;
      case "creeper":
        $this->playCreeperCard($player_id, $card);
        // Creepers are played automatically when drawn in any state,
        // so we must stay in whatever the current state is
        $continuePlayTransition = null;
        break;
      default:
        die("Not implemented: Card type $card_type does not exist");
        break;
    }

    self::setGameStateValue("cardIdSurpriseTarget", -1);

    if ($incrementPlayedCards) {
      $game->incGameStateValue("playedCards", 1);
    }

    // check creeper abilities to resolve (unless we still need to resolve the card played)
    if ($stateTransition == null && !$postponeCreeperResolve) {
      if ($game->checkCreeperResolveNeeded($card)) {
        return;
      }
    }

    // A card has been played: do we have a new winner?
    $game->checkWinConditions();

    // if not, maybe the card played had effect for any of the bonus conditions?
    $game->checkBonusConditions($player_id);

    if ($stateTransition != null) {
      // player must resolve something before continuing to play more cards
      $game->gamestate->nextstate($stateTransition);
    } elseif ($continuePlayTransition != null) {
      // else: just let player continue playing cards
      // but explicitly set state again to force args refresh
      $game->gamestate->nextstate($continuePlayTransition);
    }
  }

  public function playKeeperCard($player_id, $card)
  {
    $game = Utils::getGame();

    $game->cards->moveCard($card["id"], "keepers", $player_id);

    // Notify all players about the keeper played
    $keeperCard = KeeperCardFactory::getCard($card["id"], $card["type_arg"]);
    $game->notifyAllPlayers(
      "keeperPlayed",
      clienttranslate('${player_name} plays keeper <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $keeperCard->getName(),
        "card" => $card,
        "handCount" => $game->cards->countCardInLocation("hand", $player_id),
        "creeperCount" => Utils::getPlayerCreeperCount($player_id),
      ]
    );
    // few keepers also have immediate effect
    $keeperCard->immediateEffectOnPlay($player_id);
  }

  public function playCreeperCard($player_id, $card)
  {
    $game = Utils::getGame();

    // creepers go to table on same location as keepers
    $game->cards->moveCard($card["id"], "keepers", $player_id);

    $forcedCard = $game->getCardDefinitionFor($card);
    $game->notifyPlayer($player_id, "forcedCardNotification", "", [
      "card_trigger" => clienttranslate("Creeper"),
      "card_forced" => $forcedCard->getName(),
    ]);

    // Notify all players about the creeper played
    $creeperCard = CreeperCardFactory::getCard($card["id"], $card["type_arg"]);
    $game->notifyAllPlayers(
      "creeperPlayed",
      clienttranslate('${player_name} must place creeper <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => Utils::getPlayerName($player_id),
        "player_id" => $player_id,
        "card_name" => $creeperCard->getName(),
        "card" => $card,
        "handCount" => $game->cards->countCardInLocation("hand", $player_id),
        "creeperCount" => Utils::getPlayerCreeperCount($player_id),
      ]
    );
  }

  public function playGoalCard($player_id, $card)
  {
    $game = Utils::getGame();

    // Notify all players about the goal played
    $goalCard = GoalCardFactory::getCard($card["id"], $card["type_arg"]);

    // this goal card is still in hand at this time
    $handCount = $game->cards->countCardInLocation("hand", $player_id) - 1;

    $game->notifyAllPlayers(
      "goalPlayed",
      clienttranslate('${player_name} sets a new goal <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $goalCard->getName(),
        "card" => $card,
        "handCount" => $handCount,
      ]
    );

    $existingGoalCount = $game->cards->countCardInLocation("goals");
    $hasDoubleAgenda = Utils::getActiveDoubleAgenda();

    // No double agenda: we simply discard the oldest goal
    if (!$hasDoubleAgenda) {
      $goals = $game->cards->getCardsInLocation("goals");
      foreach ($goals as $goal_id => $goal) {
        $game->cards->playCard($goal_id);
      }

      if ($goals) {
        $game->notifyAllPlayers("goalsDiscarded", "", [
          "cards" => $goals,
          "discardCount" => $game->cards->countCardInLocation("discard"),
        ]);
      }
    }

    // We play the new goal
    $game->cards->moveCard($card["id"], "goals");

    // Fluxx FAQ:
    // Goal change and Potato move are considered to be simultaneous.
    // Basically, do both of the things (play the Goal and move the Creeper)
    // and only THEN take a look at the situation to see if you win or not.
    // So no separate win conditions check before the Potato moves.
    CreeperCardFactory::onGoalChange();

    if ($hasDoubleAgenda && $existingGoalCount > 1) {
      $game->setGameStateValue("lastGoalBeforeDoubleAgenda", $card["id"]);
      return "doubleAgendaRule";
    }
  }

  public function playRuleCard($player_id, $card)
  {
    $game = Utils::getGame();

    $game->setGameStateValue("freeRuleToResolve", -1);
    $ruleCard = RuleCardFactory::getCard($card["id"], $card["type_arg"]);
    $ruleType = $ruleCard->getRuleType();

    // Notify all players about the new rule
    // (this needs to be done before the effect, otherwise the history is confusing)
    // and so the hand count must be corrected accordingly
    $handCount = $game->cards->countCardInLocation("hand", $player_id) - 1;

    $game->notifyAllPlayers(
      "rulePlayed",
      clienttranslate('${player_name} placed a new rule: <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "card_name" => $ruleCard->getName(),
        "player_id" => $player_id,
        "ruleType" => $ruleType,
        "card" => $card,
        "handCount" => $handCount,
      ]
    );

    $location_arg = $game->getLocationArgForRuleType($ruleType);

    // Execute the immediate rule effect
    $stateTransition = $ruleCard->immediateEffectOnPlay($player_id);
    // Move card from hand to correct rules section
    $game->cards->moveCard($card["id"], "rules", $location_arg);
    // If the Rules card played resulted in any cards drawn,
    // the hand counter is incorrect (because this card was still in hand)
    // But changing order of effect/move here breaks the game play
    // Easiest fix seems to push the correct hand counters again
    $this->sendHandCountNotifications();

    return $stateTransition;
  }

  public function playActionCard($player_id, $card)
  {
    $game = Utils::getGame();

    $game->setGameStateValue("actionToResolve", -1);
    $actionCard = ActionCardFactory::getCard($card["id"], $card["type_arg"]);

    // Notify all players about the action played
    // (this needs to be done before the effect, otherwise the history is confusing)
    // and so the hand + discard count must be corrected accordingly
    $handCount = $game->cards->countCardInLocation("hand", $player_id) - 1;
    $discardCount = $game->cards->countCardInLocation("discard") + 1;

    $game->notifyAllPlayers(
      "actionPlayed",
      clienttranslate('${player_name} plays an action: <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "player_id" => $player_id,
        "card_name" => $actionCard->getName(),
        "card" => $card,
        "handCount" => $handCount,
        "discardCount" => $discardCount,
      ]
    );

    // We play the new action card
    $game->cards->playCard($card["id"]);

    // execute the action immediate effect
    $stateTransition = $actionCard->immediateEffectOnPlay($player_id);

    return $stateTransition;
  }
  
}
