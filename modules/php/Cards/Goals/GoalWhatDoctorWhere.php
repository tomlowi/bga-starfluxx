<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalWhatDoctorWhere extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("What Doctor? Where?");
    $this->subtitle = clienttranslate("The Doctor + The Time Traveler");

    $this->keeper1 = 18;
    $this->keeper2 = 21;
  }
}
