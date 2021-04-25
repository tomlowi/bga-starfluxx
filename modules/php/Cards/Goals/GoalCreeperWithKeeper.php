<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalCreeperWithKeeper extends GoalCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->set = "creeperpack";
    $this->creeper = -1;
    $this->keeper = -1;
  }

  public function goalReachedByPlayer()
  {
    $winner_id = $this->checkCreeperWithKeeper($this->creeper, $this->keeper);

    return $winner_id;
  }

  function checkCreeperWithKeeper($creeper, $keeper)
  {
    $cards = Utils::getGame()->cards;

    $creeper_card = array_values(
      $cards->getCardsOfType("creeper", $creeper)
    )[0];
    $keeper_card = array_values($cards->getCardsOfType("keeper", $keeper))[0];

    // If both cards are not in a player's keepers section, noone wins
    if (
      $creeper_card["location"] != "keepers" or
      $keeper_card["location"] != "keepers"
    ) {
      return null;
    }

    // If both cards are in the same player's keepers, this player wins
    if ($creeper_card["location_arg"] == $keeper_card["location_arg"]) {
      return $creeper_card["location_arg"];
    }

    return null;
  }
}
