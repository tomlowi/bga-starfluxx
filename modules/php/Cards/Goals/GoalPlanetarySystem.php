<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalPlanetarySystem extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Planetary System");
    $this->subtitle = clienttranslate("Distant Planet + Small Moon");

    $this->keeper1 = 4;
    $this->keeper2 = 25;
  }
}
