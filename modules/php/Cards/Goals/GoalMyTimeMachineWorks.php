<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalMyTimeMachineWorks extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("My Time Machine Works!");
    $this->subtitle = clienttranslate("The Scientist + The Time Traveler");

    $this->keeper1 = 20;
    $this->keeper2 = 21;
  }
}
