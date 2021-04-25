<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalLaserWeapons extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Laser Weapons");
    $this->subtitle = clienttranslate("Laser Pistol + Laser Sword");

    $this->keeper1 = 9;
    $this->keeper2 = 10;
  }
}
