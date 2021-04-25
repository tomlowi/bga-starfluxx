<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalToTheStars extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("To the Stars!");
    $this->subtitle = clienttranslate("Starship + Stars");

    $this->keeper1 = 14;
    $this->keeper2 = 15;
  }
}
