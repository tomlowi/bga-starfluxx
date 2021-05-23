<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalTwoKeepers extends GoalCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
    $this->keeper1 = -1;
    $this->keeper2 = -1;

    $this->keeperHolograph = 8;
  }

  public function goalReachedByPlayer()
  {
    $winner_id = $this->checkTwoKeepersWin($this->keeper1, $this->keeper2);

    return $winner_id;
  }

  function checkTwoKeepersWin($first_keeper, $second_keeper, $allow_holograph = true)
  {
    $game = Utils::getGame();
    $cards = $game->cards;
    $active_player_id = $game->getActivePlayerId();
    
    $first_keeper_card = array_values(
      $cards->getCardsOfType("keeper", $first_keeper)
    )[0];
    $second_keeper_card = array_values(
      $cards->getCardsOfType("keeper", $second_keeper)
    )[0];

    // If both keepers are not in a player's keepers, noone wins
    if (
      $first_keeper_card["location"] != "keepers" or
      $second_keeper_card["location"] != "keepers"
    ) {
      return null;
    }

    // If both keepers are in the same player's keepers, this player wins
    $first_keeper_player_id = $first_keeper_card["location_arg"];
    $second_keeper_player_id = $second_keeper_card["location_arg"];

    // Exceptionally, the Holographic projection can let player win with Keeper from someone else
    // But only in their turn, so they must be the active player!
    $holograph_player_id = null;
    $player_with_holograph = Utils::findPlayerWithKeeper($this->keeperHolograph);
    if ($allow_holograph && $player_with_holograph != null) {
      if (!Utils::checkForMalfunction($player_with_holograph["keeper_card"]["id"]))
      {
        $holograph_player_id = $player_with_holograph["player_id"];
      }      
    }

    // https://faq.looneylabs.com/fluxx-games/star-fluxx#1265
    // Active player with holographic projector takes precedence over other player that has both keepers!

    if ($holograph_player_id != null && $holograph_player_id == $active_player_id) {
      if ($first_keeper_player_id == $holograph_player_id 
          || $second_keeper_player_id == $holograph_player_id) {

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

        return $holograph_player_id;
      }      
    }
    else if ($first_keeper_player_id == $second_keeper_player_id) {
      return $first_keeper_card["location_arg"];
    }

    return null;
  }
}
