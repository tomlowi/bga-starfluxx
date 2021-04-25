<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalCityOfMonsters extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("City of Monsters");
    $this->subtitle = clienttranslate("Alien City + Bug-Eyed Monster");

    $this->keeper1 = 1;
    $this->keeper2 = 2;
  }
}
