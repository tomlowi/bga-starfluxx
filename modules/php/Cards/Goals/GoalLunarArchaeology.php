<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalLunarArchaeology extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Lunar Archaeology");
    $this->subtitle = clienttranslate("Small Moon + Monolith");

    $this->keeper1 = 25;
    $this->keeper2 = 11;
  }
}
