<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalItsFullOfStars extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("It's Full of Stars!");
    $this->subtitle = clienttranslate("Stars + Monolith");

    $this->keeper1 = 14;
    $this->keeper2 = 11;
  }
}
