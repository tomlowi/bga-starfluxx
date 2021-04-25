<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalNoTroubleAtAll extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("No Trouble at All");
    $this->subtitle = clienttranslate("Teleport Chamber + Cute Fuzzy Alien Creature");

    $this->keeper1 = 16;
    $this->keeper2 = 7;
  }
}
