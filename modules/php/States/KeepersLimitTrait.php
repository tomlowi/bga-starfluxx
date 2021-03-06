<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use starfluxx;

trait KeepersLimitTrait
{
  private function getKeepersLimit()
  {
    return self::getGameStateValue("keepersLimit");
  }

  private function getKeepersInfractions($players_id = null, $includeAllArguments = false)
  {
    $keepersLimit = $this->getKeepersLimit();

    // no active Keeper Limit, nothing to do
    if ($keepersLimit < 0 && !$includeAllArguments) {
      return [];
    }

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $keepersLimit += $addInflation;

    if ($players_id == null) {
      $players_id = array_keys(self::loadPlayersBasicInfos());
    }
    $playersInfraction = [];

    $cards = Utils::getGame()->cards;

    foreach ($players_id as $player_id) {
      $keepersInPlay = count(
        $cards->getCardsOfTypeInLocation("keeper", null, "keepers", $player_id)
      );

      $computerBonus = Utils::getActiveComputerBonus($player_id) ? 1 : 0;
      $actualKeepersLimit = $keepersLimit + $computerBonus;

      if ($keepersInPlay > $actualKeepersLimit) {
        $playersInfraction[$player_id] = [
          "discardCount" => $keepersInPlay - $actualKeepersLimit,
        ];
      } else if ($includeAllArguments) {
        $playersInfraction[$player_id] = [
          "discardCount" => 0,
        ];
      }
    }

    return $playersInfraction;
  }

  public function st_enforceKeepersLimitForOthers()
  {
    $playersInfraction = $this->getKeepersInfractions();

    // The keepers limit doesn't apply to the active player.
    $active_player_id = self::getActivePlayerId();

    if (array_key_exists($active_player_id, $playersInfraction)) {
      unset($playersInfraction[$active_player_id]);
    }

    $gamestate = Utils::getGame()->gamestate;

    // Activate all players that need to remove keepers (if any)
    $stateTransition = "keeperLimitChecked";
    if (empty($playersInfraction)) {
      $gamestate->setAllPlayersNonMultiactive($stateTransition);
    } else {
      $gamestate->setPlayersMultiactive(array_keys($playersInfraction), $stateTransition, true);
    }
  }

  public function st_enforceKeepersLimitForSelf()
  {
    $player_id = self::getActivePlayerId();
    $playersInfraction = $this->getKeepersInfractions([$player_id]);

    $gamestate = Utils::getGame()->gamestate;

    if (count($playersInfraction) == 0) {
      // Player is not in the infraction with the rule
      $gamestate->nextstate("keeperLimitChecked");
      return;
    }
  }

  public function arg_enforceKeepersLimitForOthers()
  {
    $warnInflation = Utils::getActiveInflation() 
      ? clienttranslate('<span class="flx-warn-inflation">(+1 Inflation)</span>')
      : "";

    $playerInfractions = $this->getKeepersInfractions(null, true);
    // make sure some arguments are here for the active player
    // normally they should never be in this state, but in some rare cases they
    // remain active very briefly and get error message:
    // Invalid or missing substitution argument for log message:
    $active_player_id = self::getActivePlayerId();
    $playerInfractions[$active_player_id] = [
      "discardCount" => 0,
    ];

    return [
      "i18n" => ["warnInflation"],
      "limit" => $this->getKeepersLimit(),
      "warnInflation" => $warnInflation,
      "_private" => $playerInfractions,
    ];
  }

  public function arg_enforceKeepersLimitForSelf()
  {
    $warnInflation = Utils::getActiveInflation() 
      ? clienttranslate('<span class="flx-warn-inflation">(+1 Inflation)</span>')
      : "";
    
    $player_id = self::getActivePlayerId();
    $playersInfraction = $this->getKeepersInfractions([$player_id]);

    return [
      "i18n" => ["warnInflation"],
      "limit" => $this->getKeepersLimit(),
      "warnInflation" => $warnInflation,
      "_private" => [
        "active" => $playersInfraction[$player_id] ?? ["discardCount" => 0],
      ],
    ];
  }

  /*
   * Player discards a nr of cards for keeper limit
   */
  function action_discardKeepers($cards_id)
  {
    $game = Utils::getGame();

    // possible multiple active state, so use currentPlayer rather than activePlayer
    $game->gamestate->checkPossibleAction("discardKeepers");
    $player_id = self::getCurrentPlayerId();

    $playersInfraction = $this->getKeepersInfractions([$player_id]);
    $expectedCount = $playersInfraction[$player_id]["discardCount"];
    if (count($cards_id) != $expectedCount) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate("Wrong number of cards. Expected: ") . $expectedCount
      );
    }

    // discard selected keepers, but make sure attached creepers also get discarded
    $cards = [];
    foreach ($cards_id as $card_id) {
      // Verify card is in the right location
      $card = $this->cards->getCard($card_id);
      if (
        $card == null ||
        $card["type"] != "keeper" ||
        $card["location"] != "keepers" ||
        $card["location_arg"] != $player_id
      ) {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate("Impossible discard: invalid keeper card ") . $card_id
        );
      }

      Utils::discardKeeperFromPlay($player_id, $card,
        $player_id, "keeperlimit", '');

      $cards[$card["id"]] = $card;
    }  


    $state = $game->gamestate->state();

    $stateTransition = "keeperLimitChecked";
    if ($state["type"] == "multipleactiveplayer") {
      // Multiple active state: this player is done
      $game->gamestate->setPlayerNonMultiactive($player_id, $stateTransition);
    } else {
      $game->gamestate->nextstate($stateTransition);
    }
  }
}
