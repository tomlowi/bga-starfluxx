<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalCityOfRobots extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("City of Robots");
    $this->subtitle = clienttranslate("Alien City + The Robot");

    $this->keeper1 = 1;
    $this->keeper2 = 24;
  }
}
