<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalWeNeedMorePower extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("We Need More Power!");
    $this->subtitle = clienttranslate("The Engineer + Energy Crystals");

    $this->keeper1 = 19;
    $this->keeper2 = 6;
  }
}
