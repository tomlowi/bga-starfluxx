<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalSeekingNewCivilizations extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Seeking New Civilizations");
    $this->subtitle = clienttranslate("Distant Planet + Alien City");

    $this->keeper1 = 4;
    $this->keeper2 = 1;
  }
}
