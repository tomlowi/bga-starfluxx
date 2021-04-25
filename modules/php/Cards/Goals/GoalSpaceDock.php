<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalSpaceDock extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Space Dock");
    $this->subtitle = clienttranslate("Starship + Space Station");

    $this->keeper1 = 15;
    $this->keeper2 = 13;
  }
}
