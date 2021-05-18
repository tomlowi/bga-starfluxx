<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use starfluxx;

trait HandLimitTrait
{
  private function getHandLimit()
  {
    return self::getGameStateValue("handLimit");
  }

  private function getHandInfractions($players_id = null)
  {
    $handLimit = $this->getHandLimit();

    // no active Hand Limit, nothing to do
    if ($handLimit < 0) {
      return [];
    }

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $handLimit += $addInflation;

    if ($players_id == null) {
      $players_id = array_keys(self::loadPlayersBasicInfos());
    }
    $playersInfraction = [];

    $cards = Utils::getGame()->cards;

    foreach ($players_id as $player_id) {
      $handCount = $cards->countCardInLocation("hand", $player_id);

      $computerBonus = Utils::getActiveComputerBonus($player_id) ? 1 : 0;
      $actualHandLimit = $handLimit + $computerBonus;

      if ($handCount > $actualHandLimit) {
        $playersInfraction[$player_id] = [
          "discardCount" => $handCount - $actualHandLimit,
          "actualLimit" => $actualHandLimit,          
        ];
      }
    }

    return $playersInfraction;
  }

  public function st_enforceHandLimitForOthers()
  {
    $playersInfraction = $this->getHandInfractions();

    // The hand limit doesn't apply to the active player.
    $active_player_id = self::getActivePlayerId();

    if (array_key_exists($active_player_id, $playersInfraction)) {
      unset($playersInfraction[$active_player_id]);
    }

    $gamestate = Utils::getGame()->gamestate;

    // Activate all players that need to discard some cards (if any)
    $stateTransition = "handLimitChecked";
    if (empty($playersInfraction)) {
      $gamestate->setAllPlayersNonMultiactive($stateTransition);
    } else {
      $gamestate->setPlayersMultiactive(array_keys($playersInfraction), $stateTransition, true);
    }  
  }

  public function st_enforceHandLimitForSelf()
  {
    $player_id = self::getActivePlayerId();
    $playersInfraction = $this->getHandInfractions([$player_id]);

    $gamestate = Utils::getGame()->gamestate;

    if (count($playersInfraction) == 0) {
      // Player is not in the infraction with the rule
      $gamestate->nextstate("handLimitChecked");
      return;
    }
  }

  public function arg_enforceHandLimitForOthers()
  {
    $warnInflation = Utils::getActiveInflation() 
      ? clienttranslate('<span class="flx-warn-inflation">(+1 Inflation)</span>')
      : "";

    $playerInfractions = $this->getHandInfractions();
    // make sure some arguments are here for the active player
    // normally they should never be in this state, but in some rare cases they
    // remain active very briefly and get error message:
    // Invalid or missing substitution argument for log message:
    // ${you} can only keep ${_private.actualLimit} card(s) (discard ${_private.discardCount}) for Hand Limit ${limit}${warnInflation}
    $active_player_id = self::getActivePlayerId();
    $playerInfractions[$active_player_id] = [
      "discardCount" => 0,
      "actualLimit" => -1,          
    ];

    return [
      "i18n" => ["warnInflation"],
      "limit" => $this->getHandLimit(),
      "warnInflation" => $warnInflation,
      "_private" => $playerInfractions,
    ];
  }

  public function arg_enforceHandLimitForSelf()
  {
    $warnInflation = Utils::getActiveInflation() 
      ? clienttranslate('<span class="flx-warn-inflation">(+1 Inflation)</span>')
      : "";

    $player_id = self::getActivePlayerId();
    $playersInfraction = $this->getHandInfractions([$player_id]);

    $out = [
      "i18n" => ["warnInflation"],
      "limit" => $this->getHandLimit(),
      "warnInflation" => $warnInflation,
      "_private" => [
        "active" => $playersInfraction[$player_id] ?? ["discardCount" => 0, "actualLimit" => -1],
      ],
    ];

    return $out;
  }

  /*
   * Player discards a nr of cards for hand limit
   */
  function action_discardHandCardsExcept($cards_id)
  {
    $game = Utils::getGame();

    // possible multiple active state, so use currentPlayer rather than activePlayer
    $game->gamestate->checkPossibleAction("discardHandCardsExcept");
    $player_id = self::getCurrentPlayerId();

    $playersInfraction = $this->getHandInfractions([$player_id]);

    $keepCards_id = $cards_id;    
    $discardCount = $playersInfraction[$player_id]["discardCount"];
    $handCount = $game->cards->countCardInLocation("hand", $player_id);
    $expectedCount = $handCount - $discardCount;

    if (count($keepCards_id) != $expectedCount) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate("Wrong number of cards. Expected: ") . $expectedCount
      );
    }

    $handCards = $game->cards->getCardsInLocation("hand", $player_id);
    // all cards passed to keep must be in player's hand
    foreach ($keepCards_id as $keep_card_id) {
      if (!array_key_exists($keep_card_id, $handCards)) {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate("You do not have this card in hand")
        );
      }
    }

    // all other hand cards will be discarded    
    $discards_id = [];    
    foreach ($handCards as $card_id => $card) {
      if (in_array($card_id, $keepCards_id))
        continue; // card to keep
      $discards_id[] = $card_id;
    }

    $cards = self::discardCardsFromLocation(
      $discards_id,
      "hand",
      $player_id,
      null
    );

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $player_id,
      "cards" => $cards,
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $player_id),
    ]);

    $state = $game->gamestate->state();

    $stateTransition = "handLimitChecked";
    if ($state["type"] == "multipleactiveplayer") {
      // Multiple active state: this player is done
      $game->gamestate->setPlayerNonMultiactive($player_id, $stateTransition);
    } else {
      $game->gamestate->nextstate($stateTransition);
    }
  }
}
