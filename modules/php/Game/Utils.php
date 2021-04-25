<?php
namespace StarFluxx\Game;
use StarFluxx\Cards\Rules\RulePartyBonus;
use StarFluxx\Cards\Rules\RulePoorBonus;
use StarFluxx\Cards\Rules\RuleRichBonus;
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

  public static function useCreeperPackExpansion()
  {
    return true;
  }

  public static function getActiveDoubleAgenda()
  {
    return 0 != self::getGame()->getGameStateValue("activeDoubleAgenda");
  }

  public static function getActiveInflation()
  {
    return 0 != self::getGame()->getGameStateValue("activeInflation");
  }

  public static function getActiveNoHandBonus()
  {
    return 0 != self::getGame()->getGameStateValue("activeNoHandBonus");
  }

  public static function getActivePartyBonus()
  {
    return 0 != self::getGame()->getGameStateValue("activePartyBonus");
  }

  public static function getActivePoorBonus()
  {
    return 0 != self::getGame()->getGameStateValue("activePoorBonus");
  }

  public static function getActiveRichBonus()
  {
    return 0 != self::getGame()->getGameStateValue("activeRichBonus");
  }

  public static function getActiveFirstPlayRandom()
  {
    return 0 != self::getGame()->getGameStateValue("activeFirstPlayRandom");
  }

  public static function getActiveSilverLining()
  {
    return 0 != self::getGame()->getGameStateValue("activeSilverLining");
  }

  public static function getActiveBakedPotato()
  {
    return 0 != self::getGame()->getGameStateValue("activeBakedPotato");
  }

  public static function isPartyInPlay()
  {
    $party_keeper_card_id = 16;
    $party_keeper_card = array_values(
      self::getGame()->cards->getCardsOfType("keeper", $party_keeper_card_id)
    )[0];
    return $party_keeper_card["location"] == "keepers";
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
    if (!self::useCreeperPackExpansion()) {
      return 0;
    }

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

  public static function playerHasNotYetPartiedInTurn()
  {
    // Party bonus can only be scored once by the same player in one turn.
    return 0 == Utils::getGame()->getGameStateValue("playerTurnUsedPartyBonus");
  }

  public static function playerHasNotYetBeenPoorInTurn()
  {
    // Poor bonus can only be scored once by the same player in one turn.
    return 0 == Utils::getGame()->getGameStateValue("playerTurnUsedPoorBonus");
  }

  public static function calculatePartyBonus($player_id)
  {
    $partyBonus = 0;

    if (
      Utils::getActivePartyBonus() &&
      Utils::playerHasNotYetPartiedInTurn() &&
      Utils::isPartyInPlay()
    ) {
      $addInflation = Utils::getActiveInflation() ? 1 : 0;

      $partyBonus = 1 + $addInflation;
      RulePartyBonus::notifyActiveFor($player_id, true);
      Utils::getGame()->setGameStateValue("playerTurnUsedPartyBonus", 1);
    }

    return $partyBonus;
  }

  public static function checkForPartyBonus($player_id)
  {
    $partyBonus = Utils::calculatePartyBonus($player_id);
    if ($partyBonus > 0) {
      Utils::getGame()->performDrawCards($player_id, $partyBonus);
    }
  }

  public static function calculatePoorBonus($player_id)
  {
    $poorBonus = 0;

    if (
      Utils::getActivePoorBonus() &&
      Utils::playerHasNotYetBeenPoorInTurn() &&
      Utils::hasLeastKeepers($player_id)
    ) {
      $addInflation = Utils::getActiveInflation() ? 1 : 0;

      $poorBonus = 1 + $addInflation;
      RulePoorBonus::notifyActiveFor($player_id);
      Utils::getGame()->setGameStateValue("playerTurnUsedPoorBonus", 1);
    }

    return $poorBonus;
  }

  public static function checkForPoorBonus($player_id)
  {
    $poorBonus = Utils::calculatePoorBonus($player_id);
    if ($poorBonus > 0) {
      Utils::getGame()->performDrawCards($player_id, $poorBonus);
    }
  }

  public static function playerHasNotYetUsedGoalMill()
  {
    // Goal Mill can only be used once by the same player in one turn.
    return 0 == Utils::getGame()->getGameStateValue("playerTurnUsedGoalMill");
  }

  public static function playerHasNotYetUsedMysteryPlay()
  {
    // Mystery Play can only be used once by the same player in one turn.
    return 0 ==
      Utils::getGame()->getGameStateValue("playerTurnUsedMysteryPlay");
  }

  public static function playerHasNotYetUsedRecycling()
  {
    // Recycling can only be used once by the same player in one turn.
    return 0 == Utils::getGame()->getGameStateValue("playerTurnUsedRecycling");
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
    $partyBonus =
      Utils::getActivePartyBonus() && Utils::isPartyInPlay()
        ? 1 + $addInflation
        : 0;
    if ($partyBonus > 0 && $withNotifications) {
      RulePartyBonus::notifyActiveFor($player_id, false);
    }
    $richBonus =
      Utils::getActiveRichBonus() && Utils::hasMostKeepers($player_id)
        ? 1 + $addInflation
        : 0;
    if ($richBonus > 0 && $withNotifications) {
      RuleRichBonus::notifyActiveFor($player_id);
    }

    // Play All but 1 is also affected by Inflation and Bonus rules
    if ($playRule < 0) {
      $playRule -= $addInflation;
      // if "Play All but ..." + bonus plays becomes >= 0, it actually becomes "Play All"
      if ($playRule + $partyBonus + $richBonus >= 0) {
        return PLAY_COUNT_ALL;
      }
      // else it stays "Play All but ..."
      return $playRule + $partyBonus + $richBonus;
    }
    // Normal Play Rule
    else {
      $playRule += $addInflation + $partyBonus + $richBonus;
    }

    return $playRule;
  }
}
