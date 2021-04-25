<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalStarshipFuel extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Starship Fuel");
    $this->subtitle = clienttranslate("Starship + Energy Crystals");

    $this->keeper1 = 15;
    $this->keeper2 = 6;
  }
}
