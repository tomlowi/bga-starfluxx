<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalLasersOnStun extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Lasers on Stun!");
    $this->subtitle = clienttranslate("Laser Pistol + The Captain");

    $this->keeper1 = 9;
    $this->keeper2 = 17;
  }
}
