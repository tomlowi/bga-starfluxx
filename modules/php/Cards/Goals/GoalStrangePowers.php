<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalStrangePowers extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Strange Powers");
    $this->subtitle = clienttranslate("Unseen Force + Energy Being");

    $this->keeper1 = 23;
    $this->keeper2 = 5;
  }
}
