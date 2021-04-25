<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalImDepressed extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("I'm Depressed");
    $this->subtitle = clienttranslate("Intergalactic Travel Guide + The Robot");

    $this->keeper1 = 22;
    $this->keeper2 = 24;
  }
}
