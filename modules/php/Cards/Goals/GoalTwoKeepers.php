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
  }

  public function goalReachedByPlayer()
  {
    $winner_id = $this->checkTwoKeepersWin($this->keeper1, $this->keeper2);

    return $winner_id;
  }

  function checkTwoKeepersWin($first_keeper, $second_keeper)
  {
    $cards = Utils::getGame()->cards;

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
    if (
      $first_keeper_card["location_arg"] == $second_keeper_card["location_arg"]
    ) {
      return $first_keeper_card["location_arg"];
    }

    return null;
  }
}
