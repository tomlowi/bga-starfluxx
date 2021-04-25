<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalStarWarriors extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Star Warriors");
    $this->subtitle = clienttranslate("Unseen Force + Laser Sword");

    $this->keeper1 = 23;
    $this->keeper2 = 10;
  }
}
