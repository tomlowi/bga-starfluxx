<?php
namespace StarFluxx\Game;
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

    // Play All but 1 is also affected by Inflation and Bonus rules
    if ($playRule < 0) {
      $playRule -= $addInflation;
      // if "Play All but ..." + bonus plays becomes >= 0, it actually becomes "Play All"
      if ($playRule >= 0) {
        return PLAY_COUNT_ALL;
      }
      // else it stays "Play All but ..."
      return $playRule;
    }
    // Normal Play Rule
    else {
      $playRule += $addInflation;
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

  public static function moveKeeperToPlayer($active_player_id, $card,
    $origin_player_id, $destination_player_id, $notificationMsg) {

    $game = Utils::getGame();
    // move this keeper from one player to another
    $card_definition = $game->getCardDefinitionFor($card);
    $game->cards->moveCard($card["id"], "keepers", $destination_player_id);

    $players = $game->loadPlayersBasicInfos();
    $active_player_name = $players[$active_player_id]["player_name"];
    $origin_player_name = $players[$origin_player_id]["player_name"];
    $destination_player_name = $players[$destination_player_id]["player_name"];

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
        "player_name1" => $origin_player_name,
        "player_name2" => $destination_player_name,
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
  }

  public static function discardKeeperFromPlay($active_player_id, $card,
    $origin_player_id, $trigger_name, $notificationMsg) {

    $game = Utils::getGame();
    // move this keeper from player to the discard pile
    $card_definition = $game->getCardDefinitionFor($card);
    $game->cards->playCard($card["id"]);

    $players = $game->loadPlayersBasicInfos();
    $active_player_name = $players[$active_player_id]["player_name"];
    $origin_player_name = $players[$origin_player_id]["player_name"];

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
        "i18n" => ["card_name"],
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
  }
}
