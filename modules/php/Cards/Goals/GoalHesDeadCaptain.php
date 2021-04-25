<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalHesDeadCaptain extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("He's Dead, Captain");
    $this->subtitle = clienttranslate("The Doctor + The Captain");

    $this->keeper1 = 18;
    $this->keeper2 = 17;
  }
}
