<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalTwoCreepers extends GoalCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->set = "creeperpack";
    $this->creeper1 = -1;
    $this->creeper2 = -1;
  }

  public function goalReachedByPlayer()
  {
    $winner_id = $this->checkTwoCreepersWin($this->creeper1, $this->creeper2);

    return $winner_id;
  }

  function checkTwoCreepersWin($first_creeper, $second_creeper)
  {
    $cards = Utils::getGame()->cards;

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

    // If both creepers are in the same player's keepers, this player wins
    if (
      $first_creeper_card["location_arg"] ==
      $second_creeper_card["location_arg"]
    ) {
      return $first_creeper_card["location_arg"];
    }

    return null;
  }
}
