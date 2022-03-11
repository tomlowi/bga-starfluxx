<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalAlienLifeForms extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Alien Life Forms");
    $this->subtitle = clienttranslate("Any 2 of: Energy Being, Bug-Eyed Monster, or Cute Fuzzy Alien Creature");

    $this->alien1 = 2;
    $this->alien2 = 5;
    $this->alien3 = 7;
  }

  public function goalReachedByPlayer()
  {
    $game = Utils::getGame();
    // Exceptionally, the Holographic projection can let player win with Keeper from someone else
    // But only in their turn, so they must be the active player!
    $active_player_id = $game->getActivePlayerId();
    $holograph_player_id = null;
    $player_with_holograph = Utils::findPlayerWithKeeper($this->keeperHolograph);
    if ($player_with_holograph != null && $player_with_holograph["player_id"] == $active_player_id) {
      if (!Utils::checkForMalfunction($player_with_holograph["keeper_card"]["id"]))
      {
        $holograph_player_id = $player_with_holograph["player_id"];
      }      
    }

    $winner_id = null;
    // https://faq.looneylabs.com/fluxx-games/star-fluxx#1265
    // Active player with holographic projector takes precedence over other player that has both keepers!

    if ($holograph_player_id != null) {
      // If player with active Holograph has at least 1 of the keepers, and can project
      // another one without creepers, they win
      $winner_id = $this->checkHolographWinFor($player_with_holograph);
    }

    // No Holographic Projector win to account for: just check if someone has 2 of 3 keepers
    if ( $winner_id == null) {
      $winner_id = $this->checkTwoKeepersWin($this->alien1, $this->alien2);
    }
    if ( $winner_id == null) {
      $winner_id = $this->checkTwoKeepersWin($this->alien1, $this->alien3);
    }
    if ( $winner_id == null) {
      $winner_id = $this->checkTwoKeepersWin($this->alien2, $this->alien3);
    }

    return $winner_id;
  }

  private function checkHolographWinFor($player_with_holograph)
  {
    $game = Utils::getGame();
    $holograph_player_id = $player_with_holograph["player_id"];
    $tester_id = $holograph_player_id;
    $winner_id = null;

    // if Holographic player win would be prevented by creepers anyway,
    // no need to check anything
    if ($this->isWinPreventedByCreepers($tester_id, $this)) {
      return null;
    }

    $player_with_alien1 = Utils::findPlayerWithKeeper($this->alien1);
    $player_with_alien2 = Utils::findPlayerWithKeeper($this->alien2);
    $player_with_alien3 = Utils::findPlayerWithKeeper($this->alien3);

    $alien1_owned = $player_with_alien1 != null && $tester_id == $player_with_alien1["player_id"];
    $alien1_can_holograph = $player_with_alien1 != null 
      && 0 == Utils::countNumberOfCreeperAttached($player_with_alien1["keeper_card"]["id"]);

    $alien2_owned = $player_with_alien2 != null && $tester_id == $player_with_alien2["player_id"];
    $alien2_can_holograph = $player_with_alien2 != null 
        && 0 == Utils::countNumberOfCreeperAttached($player_with_alien2["keeper_card"]["id"]);

    $alien3_owned = $player_with_alien3 != null && $tester_id == $player_with_alien3["player_id"];
    $alien3_can_holograph = $player_with_alien3 != null 
          && 0 == Utils::countNumberOfCreeperAttached($player_with_alien3["keeper_card"]["id"]);

    if (
      ($alien1_owned && ($alien2_can_holograph || $alien3_can_holograph))
      || ($alien2_owned && ($alien1_can_holograph || $alien3_can_holograph))
      || ($alien3_owned && ($alien1_can_holograph || $alien2_can_holograph))
     ) {
      $winner_id = $tester_id;

      $players = $game->loadPlayersBasicInfos();
      $holograph_player_name = $players[$holograph_player_id]["player_name"];

      $card_definition = $game->getCardDefinitionFor($player_with_holograph["keeper_card"]);

      $game->notifyAllPlayers(
        "winWithHolograph",
        clienttranslate(
          '<b>${card_name}</b> allows ${player_name} to win with Keeper from another player'
        ),
        [
          "i18n" => ["card_name"],
          "player_name" => $holograph_player_name,
          "card_name" => $card_definition->getName(),
        ]
      );
    }

    return $winner_id;
  }
}
