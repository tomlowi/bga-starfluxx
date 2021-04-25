<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalThatsNoMoon extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("That's No Moon");
    $this->subtitle = clienttranslate("Small Moon + Space Station");

    $this->keeper1 = 25;
    $this->keeper2 = 13;
  }
}
