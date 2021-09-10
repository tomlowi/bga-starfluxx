<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalTwoCreepers extends GoalCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->creeper1 = -1;
    $this->creeper2 = -1;

    $this->keeperHolograph = 8;
  }

  public function goalReachedByPlayer()
  {
    $winner_id = $this->checkTwoCreepersWin($this->creeper1, $this->creeper2);

    return $winner_id;
  }

  function checkTwoCreepersWin($first_creeper, $second_creeper)
  {
    $game = Utils::getGame();
    $cards = $game->cards;
    $active_player_id = $game->getActivePlayerId();

    $first_creeper_card = array_values(
      $cards->getCardsOfType("creeper", $first_creeper)
    )[0];
    $second_creeper_card = array_values(
      $cards->getCardsOfType("creeper", $second_creeper)
    )[0];

    // If both creepers are not in a player's keepers section, noone wins
    if (
      $first_creeper_card["location"] != "keepers" or
      $second_creeper_card["location"] != "keepers"
    ) {
      return null;
    }

    // Exceptionally, the Holographic projection can let player win with Keeper from someone else
    // But only in their turn, so they must be the active player!
    $holograph_player_id = null;
    $player_with_holograph = Utils::findPlayerWithKeeper($this->keeperHolograph);
    if ($player_with_holograph != null) {
      if (!Utils::checkForMalfunction($player_with_holograph["keeper_card"]["id"]))
      {
        $holograph_player_id = $player_with_holograph["player_id"];
      }      
    }
    // Specifically, player with holograph can also win
    // if one or even both of required creepers are attached to the same keeper of another player!
    // (providing no other creepers are attached to the same keeper also, preventing the win)
    $first_creeper_player_id = $first_creeper_card["location_arg"];
    $second_creeper_player_id = $second_creeper_card["location_arg"];

    $holograph_win = false;
    if ($holograph_player_id != null && $holograph_player_id == $active_player_id) 
    {
      $creeper1_def = $game->getCardDefinitionFor($first_creeper_card);
      $creeper2_def = $game->getCardDefinitionFor($second_creeper_card);

      if ($holograph_player_id == $first_creeper_player_id
          && $first_creeper_player_id != $second_creeper_player_id)
      { // if second creeper is attached to some keeper (as only one) => holograph can win
        $creeper2_attachedTo = $creeper2_def->isAttachedTo();
        $holograph_win = ($creeper2_attachedTo > -1)
          && Utils::countNumberOfCreeperAttached($creeper2_attachedTo) == 1;
      }
      else if ($holograph_player_id == $second_creeper_player_id
        && $first_creeper_player_id != $second_creeper_player_id)
      { // if first creeper is attached to some keeper (as only one) => holograph can win
        $creeper1_attachedTo = $creeper1_def->isAttachedTo();
        $holograph_win = ($creeper1_attachedTo > -1)
          && Utils::countNumberOfCreeperAttached($creeper1_attachedTo) == 1;
      }
      else if ($first_creeper_player_id == $second_creeper_player_id
        && $holograph_player_id != $first_creeper_player_id)
      { // both creepers are together with another player: 
        // if they are attached to the same keeper => holograph can win
        $creeper1_attachedTo = $creeper1_def->isAttachedTo();
        $holograph_win = ($creeper1_attachedTo > -1 && 
          $creeper1_attachedTo == $creeper2_def->isAttachedTo());
      }
    }

    // Holograph win
    if ($holograph_win)
    {
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

    // Normal win:
    // If both creepers are in the same player's keepers, this player wins
    if ($first_creeper_player_id == $second_creeper_player_id)
    {
      return $first_creeper_player_id;
    }

    return null;
  }
}
