<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalFortyTwo extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Forty-Two");
    $this->subtitle = clienttranslate("The Computer + Intergalactic Travel Guide");

    $this->keeper1 = 3;
    $this->keeper2 = 22;
  }
}
