<?php
namespace StarFluxx\Game;
use StarFluxx\Cards\Keepers\KeeperTheComputer;
use starfluxx;

class Utils
{
  public static function getGame()
  {
    return starfluxx::get();
  }

  public static function throwInvalidUserAction($msg)
  {
    throw new \BgaUserException($msg);
  }

  public static function getActiveDoubleAgenda()
  {
    return 0 != self::getGame()->getGameStateValue("activeDoubleAgenda");
  }

  public static function getActiveInflation()
  {
    return 0 != self::getGame()->getGameStateValue("activeInflation");
  }

  private static function getAllPlayersKeeperCount()
  {
    // We cannot just use "countCardsByLocationArgs" here because it doesn't return
    // any value for players without keepers
    $players = Utils::getGame()->loadPlayersBasicInfos();
    $cards = Utils::getGame()->cards;

    $keeperCounts = [];
    foreach ($players as $player_id => $player) {
      // Count each player keepers
      $nbKeepers = count(
        $cards->getCardsOfTypeInLocation("keeper", null, "keepers", $player_id)
      );
      $keeperCounts[$player_id] = $nbKeepers;
    }

    return $keeperCounts;
  }

  public static function getPlayerName($player_id)
  {
    $game = Utils::getGame();
    $players = $game->loadPlayersBasicInfos();
    return $players[$player_id]["player_name"];
  }

  public static function getPlayerKeeperCount($player_id)
  {
    $cards = Utils::getGame()->cards;
    $nbCreepers = count(
      $cards->getCardsOfTypeInLocation("keeper", null, "keepers", $player_id)
    );
    return $nbCreepers;
  }

  public static function getPlayerCreeperCount($player_id)
  {
    $cards = Utils::getGame()->cards;
    $nbCreepers = count(
      $cards->getCardsOfTypeInLocation("creeper", null, "keepers", $player_id)
    );
    return $nbCreepers;
  }

  public static function hasLeastKeepers($active_player_id)
  {
    $keepersCounts = self::getAllPlayersKeeperCount();

    $activeKeepersCount = $keepersCounts[$active_player_id];

    unset($keepersCounts[$active_player_id]);

    // no ties, only 1 player should have the least
    foreach ($keepersCounts as $player_id => $keepersCount) {
      if ($keepersCount <= $activeKeepersCount) {
        return false;
      }
    }
    return true;
  }

  public static function hasMostKeepers($active_player_id)
  {
    $keepersCounts = self::getAllPlayersKeeperCount();

    $activeKeepersCount = $keepersCounts[$active_player_id];

    unset($keepersCounts[$active_player_id]);

    // no ties, only 1 player should have the most
    foreach ($keepersCounts as $player_id => $keepersCount) {
      if ($keepersCount >= $activeKeepersCount) {
        return false;
      }
    }
    return true;
  }

  public static function playerHasNotYetUsedWormhole()
  {
    // Wormhole can only be used once by the same player in one turn.
    return 0 ==
      Utils::getGame()->getGameStateValue("playerTurnUsedWormhole");
  }

  public static function playerHasNotYetUsedCaptain()
  {
    // Captain can only be used once by the same player in one turn.
    return 0 ==
      Utils::getGame()->getGameStateValue("playerTurnUsedCaptain");
  }

  public static function playerHasNotYetUsedScientist()
  {
    // Scientist can only be used once by the same player in one turn.
    return 0 ==
      Utils::getGame()->getGameStateValue("playerTurnUsedScientist");
  }

  public static function playerHasNotYetUsedLaserPistol()
  {
    // LaserPistol can only be used once by the same player in one turn.
    return 0 ==
      Utils::getGame()->getGameStateValue("playerTurnUsedLaserPistol");
  }

  public static function playerHasNotYetUsedLaserSword()
  {
    // LaserSword can only be used once by the same player in one turn.
    return 0 ==
      Utils::getGame()->getGameStateValue("playerTurnUsedLaserSword");
  }

  public static function playerHasNotYetUsedUnseenForce()
  {
    // UnseenForce can only be used once by the same player in one turn.
    return 0 ==
      Utils::getGame()->getGameStateValue("playerTurnUsedUnseenForce");
  }

  public static function playerHasNotYetUsedTeleporter()
  {
    // Teleporter can only be used once by the same player in one turn.
    return 0 ==
      Utils::getGame()->getGameStateValue("playerTurnUsedTeleporter");
  }

  public static function playerHasNotYetUsedComputerBonus()
  {
    // Computer Bonus draw only once per turn.
    return 0 ==
      Utils::getGame()->getGameStateValue("playerTurnUsedComputerBonus");
  }

  public static function getActiveComputerBonus($player_id)
  {
    $keeperComputer = 3;
    $computer_player = Utils::findPlayerWithKeeper($keeperComputer);

    return $computer_player != null && $computer_player["player_id"] == $player_id
      && !Utils::checkForMalfunction($computer_player["keeper_card"]["id"]);
  }

  public static function calculateDrawComputerBonus($player_id)
  {
    $computerBonus = 0;

    if (
      Utils::getActiveComputerBonus($player_id) &&
      Utils::playerHasNotYetUsedComputerBonus()
    ) {
      $computerBonus = 1;
      KeeperTheComputer::notifyActiveFor($player_id);
      Utils::getGame()->setGameStateValue("playerTurnUsedComputerBonus", 1);
    }

    return $computerBonus;
  }

  public static function checkForDrawComputerBonus($player_id)
  {
    $computerBonus = Utils::calculateDrawComputerBonus($player_id);
    if ($computerBonus > 0) {
      Utils::getGame()->performDrawCards($player_id, $computerBonus);
    }
  }

  public static function getActiveTempHand()
  {
    if (Utils::getGame()->getGameStateValue("tmpHand3Card") > 0) {
      return 3;
    }
    if (Utils::getGame()->getGameStateValue("tmpHand2Card") > 0) {
      return 2;
    }
    if (Utils::getGame()->getGameStateValue("tmpHand1Card") > 0) {
      return 1;
    }
    return 0;
  }

  public static function getActiveTempHandWithPlays()
  {
    if (Utils::getGame()->getGameStateValue("tmpHand3ToPlay") > 0) {
      return 3;
    }
    if (Utils::getGame()->getGameStateValue("tmpHand2ToPlay") > 0) {
      return 2;
    }
    if (Utils::getGame()->getGameStateValue("tmpHand1ToPlay") > 0) {
      return 1;
    }
    return 0;
  }

  public static function calculateCardsLeftToPlayFor($player_id)
  {
    $game = Utils::getGame();
    // calculate how many cards player should still play
    $alreadyPlayed = $game->getGameStateValue("playedCards");

    $mustPlay = Utils::calculateCardsMustPlayFor($player_id, false);

    $leftCount = $mustPlay - $alreadyPlayed;
    $handCount = $game->cards->countCardInLocation("hand", $player_id);

    if ($mustPlay >= PLAY_COUNT_ALL) {
      // Play All > left as many as cards in hand
      $leftCount = $handCount;
    } elseif ($mustPlay < 0) {
      // Play All but 1 > left as many as cards in hand minus the leftover
      $leftCount = $handCount + $mustPlay; // ok, $mustPlay is negative here
    }

    // could become < 0 if rules for already used bonus plays get discarded
    // in that case player should not play any more cards
    if ($leftCount < 0) {
      $leftCount = 0;
    }
    // can't play more cards than in hand
    elseif ($leftCount > $handCount) {
      $leftCount = $handCount;
    }

    return $leftCount;
  }

  public static function calculateCardsMustPlayFor(
    $player_id,
    $withNotifications
  ) {
    $game = Utils::getGame();
    // current basic Play rule
    $playRule = $game->getGameStateValue("playRule");

    // Play All = always Play All
    if ($playRule >= PLAY_COUNT_ALL) {
      return $playRule;
    }

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    // check bonus rules
    $computerBonus =
      Utils::getActiveComputerBonus($player_id)
        ? 1 + $addInflation
        : 0;
    if ($computerBonus > 0 && $withNotifications) {
      KeeperTheComputer::notifyActiveFor($player_id);
    }

    // Play All but 1 is also affected by Inflation and Bonus rules
    if ($playRule < 0) {
      $playRule -= $addInflation;
      // if "Play All but ..." + bonus plays becomes >= 0, it actually becomes "Play All"
      if ($playRule + $computerBonus >= 0) {
        return PLAY_COUNT_ALL;
      }
      // else it stays "Play All but ..."
      return $playRule + $computerBonus;
    }
    // Normal Play Rule
    else {
      $playRule += $addInflation + $computerBonus;
    }

    return $playRule;
  }

  public static function findPlayerWithKeeper($keeperUniqueId)
  {
    $game = Utils::getGame();
    // check who has this keeper in play now
    $keeper_card = array_values(
      $game->cards->getCardsOfType("keeper", $keeperUniqueId)
    )[0];
    // if nobody, nothing to do
    if ($keeper_card["location"] != "keepers") {
      return null;
    }

    $keeper_player_id = $keeper_card["location_arg"];
    return [
      "player_id" => $keeper_player_id,
      "keeper_card" => $keeper_card,
    ];
  }

  public static function findPlayerWithCreeper($creeperUniqueId)
  {
    $game = Utils::getGame();
    // check who has this creeper in play now
    $creeper_card = array_values(
      $game->cards->getCardsOfType("creeper", $creeperUniqueId)
    )[0];
    // if nobody, nothing to do
    if ($creeper_card["location"] != "keepers") {
      return null;
    }

    $creeper_player_id = $creeper_card["location_arg"];
    return [
      "player_id" => $creeper_player_id,
      "creeper_card" => $creeper_card,
    ];
  }

  public static function listPlayersWithSurpriseInHandFor($target_card)
  {
    $game = Utils::getGame();
    $surprise_players = [];

    $surprises = [317, 318, 319, 320, 321];
    for ($i = 0; $i < count($surprises); $i++) {
      $surprise_player = Utils::findPlayerWithSurpriseInHand($surprises[$i]);
      if ($surprise_player != null
        && Utils::checkCardIsValidSurpriseCounterFor($surprise_player["action_card"], $target_card)
      )
      {
        $surprise_players[$surprise_player["player_id"]] = $surprise_player["action_card"];
      }
    }

    return $surprise_players;
  }

  public static function otherPlayersWithSurpriseInHand($player_id)
  {
    $surprises = [317, 318, 319, 320, 321];
    for ($i = 0; $i < count($surprises); $i++) {
      $surprise_player = Utils::findPlayerWithSurpriseInHand($surprises[$i]);
      if ($surprise_player != null && $surprise_player["player_id"] != $player_id) {
        return true;
      }
    }

    return false;
  }

  public static function findPlayerWithSurpriseInHand($actionUniqueId)
  {
    $game = Utils::getGame();
    // check who has this surprise Action in hand cards now
    $action_card = array_values(
      $game->cards->getCardsOfType("action", $actionUniqueId)
    )[0];
    // if nobody, nothing to do
    if ($action_card["location"] != "hand") {
      return null;
    }

    $action_player_id = $action_card["location_arg"];
    return [
      "player_id" => $action_player_id,
      "action_card" => $action_card,
    ];
  }

  public static function checkCardIsValidSurpriseCounterFor($surprise_card, $target_card)
  {
    $game = Utils::getGame();
    $surprise_card_def = $game->getCardDefinitionFor($surprise_card);

    if ($surprise_card["type"] != "action"
      || $surprise_card_def->getActionType() != "surprise") {
        return false;
      }
    
    $target_type = $target_card["type"];
    $target_unique = $target_card["type_arg"];

    $surprise_player_id = $surprise_card["location_arg"];

    $lastStolenKeeperId = $game->getGameStateValue("cardIdStolenKeeper");
    
    $valid_surprise = false;
    switch ($target_type)
    {
      case "keeper":
        if ($lastStolenKeeperId == $target_card["id"])
        { // Last Keeper Stolen from play => can be countered with It's A Trap
          $valid_surprise = $surprise_card_def->getUniqueId() == 317;
        }
        else
        { // Keeper played from hand
          // That's Mine = 318
          $valid_surprise = $surprise_card_def->getUniqueId() == 318;
        }
        break;
      case "goal":
        // Canceled Plans = 321
        $valid_surprise = $surprise_card_def->getUniqueId() == 321;
        break;
      case "rule":
        // Veto = 319
        $valid_surprise = $surprise_card_def->getUniqueId() == 319;
        break;
      case "action":
        $target_card_def = $game->getCardDefinitionFor($target_card);
        // BelayThat = 320
        $valid_surprise = $surprise_card_def->getUniqueId() == 320
        // or It's A Trap = 317 sometimes can also be used against BeamUsUp = 311 action        
          || ($surprise_card_def->getUniqueId() == 317 && $target_unique == 311
              && Utils::checkBeamUsUpCouldTeleportBeingsFrom($surprise_player_id))
        // or if the action is a Surprise itself (played during turn),
        // it can also be countered with any other Surprise
          || ($target_card_def->getActionType() == "surprise")
          ;
        break;
    }

    return $valid_surprise;
  }

  public static function checkForMalfunction($card_id) 
  {
    $game = Utils::getGame();
    $keeper_id_malfunction = $game->getGameStateValue("creeperMalfunctionAttachedTo");

    return $card_id == $keeper_id_malfunction;
  }

  public static function checkForEvil($card_id) 
  {
    $game = Utils::getGame();
    $keeper_id_evil = $game->getGameStateValue("creeperEvilAttachedTo");

    return $card_id == $keeper_id_evil;
  }

  public static function checkForBrainParasites($card_id)
  {
    $game = Utils::getGame();
    $keeper_id_brainparasites = $game->getGameStateValue("creeperBrainParasitesAttachedTo");

    return $card_id == $keeper_id_brainparasites;
  }

  public static function countNumberOfCreeperAttached($card_id)
  {
    return (self::checkForMalfunction($card_id) ? 1 : 0)
      + (self::checkForEvil($card_id) ? 1 : 0)
      + (self::checkForBrainParasites($card_id) ? 1 : 0)
      ;
  }

  public static function checkTargetCardReplaceWithExpendableCrewman($card, $active_player_id, 
    $origin_player_id, $origin_player_name)
  {
    $game = Utils::getGame();

    $target_card = $card;
    // if targetted origin player has the Expendable Crewman,
    // and this card is a keeper or a creeper attached to a keeper,
    // then the Expendable Crewman should step into the line of fire and take the hit

    // unless the active player is the one owning the Expendable Crewman,
    // they can still give him exact order
    if ($active_player_id == $origin_player_id) {
      return $target_card;
    }

    $expendable_unique_id = 12;
    $expendable_player = self::findPlayerWithKeeper($expendable_unique_id);
    if ($expendable_player != null && $expendable_player["player_id"] == $origin_player_id) {
      $expendable_card = $expendable_player["keeper_card"];

      $stepIn = false;
      if ($card["type"] == "keeper") {
        $stepIn = true;
      }
      else if ($card["type"] == "creeper") {
        $creeper = $game->getCardDefinitionFor($card);
        $attached_id = $creeper->isAttachedTo();
        $stepIn = ($attached_id > -1);
      }

      if ($stepIn) {
        $target_card = $expendable_card;
        $card_definition = $game->getCardDefinitionFor($expendable_card);

        $game->notifyAllPlayers(
          "expendableCrewman", 
          '<b>${card_name}</b> takes the hit for ${player_name}',
          [
            "i18n" => ["card_name"],
            "player_name" => $origin_player_name,
            "card_name" => $card_definition->getName(),
          ]
        );
      }      
    }

    return $target_card;
  }

  public static function moveKeeperToPlayer($active_player_id, $card,
    $origin_player_id, $destination_player_id, $notificationMsg, $checkForReplace = true) {

    $game = Utils::getGame();

    $players = $game->loadPlayersBasicInfos();
    $active_player_name = $players[$active_player_id]["player_name"];
    $origin_player_name = $players[$origin_player_id]["player_name"];
    $destination_player_name = $players[$destination_player_id]["player_name"];

    // replace with Expendable Crewman if needed
    if ($checkForReplace) {
      $card = self::checkTargetCardReplaceWithExpendableCrewman($card, $active_player_id,
      $origin_player_id, $origin_player_name);
    }    

    // move this keeper from one player to another
    $card_definition = $game->getCardDefinitionFor($card);
    $game->cards->moveCard($card["id"], "keepers", $destination_player_id);

    $cardsToMove = [$card];

    $keeper_card = null;
    $creepers_attached = [];
    // if this card is a keeper with a creeper attached,
    // then any attached creeper(s) should move together with it
    if ($card["type"] == "keeper") {
      $keeper_card = $card;
      if ($card["id"] == $game->getGameStateValue("creeperBrainParasitesAttachedTo")) {
        $brainparasites = self::findPlayerWithCreeper(51);
        $attachedCard = $brainparasites["creeper_card"];
        $cardsToMove[] = $attachedCard;
        $game->cards->moveCard($attachedCard["id"], "keepers", $destination_player_id);

        $creepers_attached[] = 51;
      }
      if ($card["id"] == $game->getGameStateValue("creeperEvilAttachedTo")) {
        $evil = self::findPlayerWithCreeper(52);
        $attachedCard = $evil["creeper_card"];
        $cardsToMove[] = $attachedCard;
        $game->cards->moveCard($attachedCard["id"], "keepers", $destination_player_id);

        $creepers_attached[] = 52;
      }
      if ($card["id"] == $game->getGameStateValue("creeperMalfunctionAttachedTo")) {
        $malfunction = self::findPlayerWithCreeper(53);
        $attachedCard = $malfunction["creeper_card"];
        $cardsToMove[] = $attachedCard;
        $game->cards->moveCard($attachedCard["id"], "keepers", $destination_player_id);

        $creepers_attached[] = 53;
      }      
    }
    // if this card is a creeper that is attached to a keeper,
    // then that keeper should also move with it
    else if ($card["type"] == "creeper") {
      $creeper = $game->getCardDefinitionFor($card);
      $attached_id = $creeper->isAttachedTo();
      if ($attached_id > -1) {
        $attachedCard = $game->cards->getCard($attached_id);
        $cardsToMove[] = $attachedCard;
        $game->cards->moveCard($attachedCard["id"], "keepers", $destination_player_id);

        $keeper_card = $attachedCard;
        $creepers_attached[] = $creeper->getUniqueId();
      }
    }

    $game->notifyAllPlayers(
      "keepersMoved", $notificationMsg,
      [
        "i18n" => ["card_name"],
        "player_name" => $active_player_name,
        "player_name2" => $origin_player_name,
        "player_name3" => $destination_player_name,
        "card_name" => $card_definition->getName(),
        "destination_player_id" => $destination_player_id,
        "origin_player_id" => $origin_player_id,
        "cards" => $cardsToMove,
        "destination_creeperCount" => Utils::getPlayerCreeperCount($destination_player_id),
        "origin_creeperCount" => Utils::getPlayerCreeperCount($origin_player_id),
      ]
    );

    // also send notifications again to show creeper(s) being attached
    // to the moved keeper (as stock card was destroyed and re-created)

    if ($keeper_card != null && !empty($creepers_attached))
    {
      foreach ($creepers_attached as $creeper) {
        $game->notifyAllPlayers(
          "creeperAttached",
          '',
          [
            "player_id" => $destination_player_id,
            "card" => $keeper_card,
            "creeper" => $creeper,
          ]
        );
      }
    }

    // finally, if keeper is moved to the active player,
    // maybe it has an effect that should immediately apply to this new owner (like The Computer)
    if ($keeper_card != null && $destination_player_id == $active_player_id) {
      $keeperCard = $game->getCardDefinitionFor($keeper_card);
      $keeperCard->immediateEffectOnPlay($active_player_id);
    }
  }

  public static function discardKeeperFromPlay($active_player_id, $card,
    $origin_player_id, $trigger_name, $notificationMsg, $checkForReplace = true) {

    $game = Utils::getGame();

    $players = $game->loadPlayersBasicInfos();
    $active_player_name = $players[$active_player_id]["player_name"];
    $origin_player_name = $players[$origin_player_id]["player_name"];

    // replace with Expendable Crewman if needed
    if ($checkForReplace) {
      $card = self::checkTargetCardReplaceWithExpendableCrewman($card, $active_player_id, 
      $origin_player_id, $origin_player_name);
    }    

    // move this keeper from player to the discard pile
    $game->cards->playCard($card["id"]);
    $card_definition = $game->getCardDefinitionFor($card);
    $cardsToRemove = [$card];

    $keeper_card = null;
    $creepers_attached = [];
    // if this card is a keeper with a creeper attached,
    // then any attached creeper(s) should get detached and also be discarded
    if ($card["type"] == "keeper") {
      $keeper_card = $card;
      if ($card["id"] == $game->getGameStateValue("creeperBrainParasitesAttachedTo")) {
        $brainparasites = self::findPlayerWithCreeper(51);
        $attachedCard = $brainparasites["creeper_card"];
        $cardsToRemove[] = $attachedCard;
        $game->cards->playCard($attachedCard["id"]);

        $game->setGameStateValue("creeperBrainParasitesAttachedTo", -1);
        $creepers_attached[] = 51;
      }
      if ($card["id"] == $game->getGameStateValue("creeperEvilAttachedTo")) {
        $evil = self::findPlayerWithCreeper(52);
        $attachedCard = $evil["creeper_card"];
        $cardsToRemove[] = $attachedCard;
        $game->cards->playCard($attachedCard["id"]);

        $game->setGameStateValue("creeperEvilAttachedTo", -1);
        $creepers_attached[] = 52;
      }
      if ($card["id"] == $game->getGameStateValue("creeperMalfunctionAttachedTo")) {
        $malfunction = self::findPlayerWithCreeper(53);
        $attachedCard = $malfunction["creeper_card"];
        $cardsToRemove[] = $attachedCard;
        $game->cards->playCard($attachedCard["id"]);

        $game->setGameStateValue("creeperMalfunctionAttachedTo", -1);
        $creepers_attached[] = 53;
      }      
    }
    // if this card is a creeper that is attached to a keeper,
    // then that keeper should also be discarded (after detaching the creeper)
    else if ($card["type"] == "creeper") {
      $creeper = $game->getCardDefinitionFor($card);
      $attached_id = $creeper->isAttachedTo();
      if ($attached_id > -1) {
        $attachedCard = $game->cards->getCard($attached_id);
        $cardsToRemove[] = $attachedCard;
        $game->cards->playCard($attachedCard["id"]);

        $keeper_card = $attachedCard;
        $creepers_attached[] = $creeper->getUniqueId();
        $creeper->detach();
      }
    }

    // send notifications again to show creeper(s) being detached
    if ($keeper_card != null && !empty($creepers_attached))
    {
      foreach ($creepers_attached as $creeper) {
        $game->notifyAllPlayers(
          "creeperDetached",
          '',
          [
            "creeper" => $creeper,
          ]
        );
      }
    }
    // send notifications to show cards being discarded
    $game->notifyAllPlayers(
      "keepersDiscarded", $notificationMsg,
      [
        "i18n" => ["card_name", "trigger_name"],
        "player_name" => $active_player_name,
        "player_name2" => $origin_player_name,
        "trigger_name" => $trigger_name,
        "card_name" => $card_definition->getName(),
        "player_id" => $origin_player_id,
        "cards" => $cardsToRemove,
        "discardCount" => $game->cards->countCardInLocation("discard"),
        "creeperCount" => Utils::getPlayerCreeperCount($origin_player_id),
      ]
    );

    // finally, if keeper is discarded,
    // sometimes special things should happen also
    if ($keeper_card != null) {
      $keeperCard = $game->getCardDefinitionFor($keeper_card);
      $keeperCard->immediateEffectOnDiscard($active_player_id);
    }
  }

  public static function moveKeeperToHand($active_player_id, $card,
    $origin_player_id, $destination_player_id, $notificationMsg, $checkForReplace = true) {

    $game = Utils::getGame();

    $players = $game->loadPlayersBasicInfos();
    $active_player_name = $players[$active_player_id]["player_name"];
    $origin_player_name = $players[$origin_player_id]["player_name"];
    $destination_player_name = $players[$destination_player_id]["player_name"];

    // replace with Expendable Crewman if needed
    if ($checkForReplace) {
      $card = self::checkTargetCardReplaceWithExpendableCrewman($card, $active_player_id,
        $origin_player_id, $origin_player_name);
    }    

    // move this keeper from table to player hand
    $card_definition = $game->getCardDefinitionFor($card);
    $game->cards->moveCard($card["id"], "hand", $destination_player_id);

    $cardsToMove = [$card];

    $keeper_card = null;
    $creepers_attached = [];
    // if this card is a keeper with a creeper attached,
    // then any attached creeper(s) should move together with it, 
    // but then needs to get played from that hand directly!
    if ($card["type"] == "keeper") {
      $keeper_card = $card;
      if ($card["id"] == $game->getGameStateValue("creeperBrainParasitesAttachedTo")) {
        $brainparasites = self::findPlayerWithCreeper(51);
        $attachedCard = $brainparasites["creeper_card"];
        $cardsToMove[] = $attachedCard;
        $game->cards->moveCard($attachedCard["id"], "hand", $destination_player_id);

        $game->setGameStateValue("creeperBrainParasitesAttachedTo", -1);
        $creepers_attached[] = $attachedCard;
      }
      if ($card["id"] == $game->getGameStateValue("creeperEvilAttachedTo")) {
        $evil = self::findPlayerWithCreeper(52);
        $attachedCard = $evil["creeper_card"];
        $cardsToMove[] = $attachedCard;
        $game->cards->moveCard($attachedCard["id"], "hand", $destination_player_id);

        $game->setGameStateValue("creeperEvilAttachedTo", -1);
        $creepers_attached[] = $attachedCard;
      }
      if ($card["id"] == $game->getGameStateValue("creeperMalfunctionAttachedTo")) {
        $malfunction = self::findPlayerWithCreeper(53);
        $attachedCard = $malfunction["creeper_card"];
        $cardsToMove[] = $attachedCard;
        $game->cards->moveCard($attachedCard["id"], "hand", $destination_player_id);

        $game->setGameStateValue("creeperMalfunctionAttachedTo", -1);
        $creepers_attached[] = $attachedCard;
      }      
    }

    $game->notifyAllPlayers(
      "cardFromTableToHand", $notificationMsg,
      [
        "i18n" => ["card_name"],
        "card" => $card,
        "player_name" => $active_player_name,
        "player_name2" => $origin_player_name,
        "player_name3" => $destination_player_name,
        "card_name" => $card_definition->getName(),
        "player_id" => $destination_player_id,
        "destination_player_id" => $destination_player_id,
        "origin_player_id" => $origin_player_id,
        "handCount" => $game->cards->countCardInLocation("hand", $destination_player_id),
        "creeperCount" => Utils::getPlayerCreeperCount($destination_player_id),
      ]
    );

    if (!empty($creepers_attached))
    {
      foreach ($creepers_attached as $creeper_card) {
        $creeper_definition = $game->getCardDefinitionFor($creeper_card);
        $game->notifyAllPlayers(
          "cardFromTableToHand", $notificationMsg,
          [
            "i18n" => ["card_name"],
            "card" => $creeper_card,
            "player_name" => $active_player_name,
            "player_name2" => $origin_player_name,
            "player_name3" => $destination_player_name,
            "card_name" => $creeper_definition->getName(),
            "player_id" => $destination_player_id,
            "destination_player_id" => $destination_player_id,
            "origin_player_id" => $origin_player_id,
            "handCount" => $game->cards->countCardInLocation("hand", $destination_player_id),
            "creeperCount" => Utils::getPlayerCreeperCount($destination_player_id),
          ]
        );

        $game->playCreeperCard($destination_player_id, $creeper_card);
      }
    }

  }

  public static function checkPlayerHasTrap($check_player_id)
  {
    $trap = Utils::findPlayerWithSurpriseInHand(317);
    return $trap != null && $check_player_id == $trap["player_id"];
  }

  public static function checkBeamUsUpCouldTeleportBeingsFrom($check_player_id)
  {
    // https://faq.looneylabs.com/question/1621
    // It's A Trap can be used to counter Beam Us Up, *if* the Teleporter is in play
    // and the player holding the It's A Trap card in hand actually has any beings that
    // would be affected by Beam Us Up (= keepers with brains)
    $game = Utils::getGame();

    // Teleporter must be in play with some other player
    $keeperTeleporter = 16;
    $teleporter_player = Utils::findPlayerWithKeeper($keeperTeleporter);
    if ($teleporter_player == null || $teleporter_player["player_id"] == $check_player_id)
    {
      return false;
    }
    // Teleporter must be working (no Malfunction)
    if (Utils::checkForMalfunction($teleporter_player["keeper_card"]["id"]))
    {
      return false;
    }
    // This player must have keepers in play that have brains
    $player_cards = $game->cards->getCardsInLocation("keepers", $check_player_id);
    foreach ($player_cards as $card_id => $card) {
      // "beings" = keepers with brains, see https://faq.looneylabs.com/question/462
      if ($card["type"] == "keeper") {
        $card_definition = $game->getCardDefinitionFor($card);
        if ($card_definition->getKeeperType() == "brains") {
          // Yes, this player could loose keepers to the Teleporter when BeamUsUp is played
          return true;
        }
      }
    }

    // No, this player would not loose keepers to the Teleporter by BeamUsUp
    return false;
  }

  public static function checkCounterTrapForKeeperStolen($target_player_id, $target_keeper_id)
  {
    $game = Utils::getGame();
    $hasTrap = Utils::checkPlayerHasTrap($target_player_id);

    if ($hasTrap)
    {
      $game->setGameStateValue("cardIdStolenKeeper", $target_keeper_id);
      $game->setGameStateValue("cardIdSurpriseTarget", $target_keeper_id);
  
      // @TODO: check and make sure no creepers should be attached to the stolen keeper
      // until it is decided that no surprise trap will be played on it

      return "checkForSurprises";
    }

    return null;
  }
}
