<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalTheRobotsHaveTurnedAgainstUs extends GoalCreeperWithKeeper
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The Robots Have Turned Against Us");
    $this->subtitle = clienttranslate("Evil + The Robot");

    $this->creeper = 52;
    $this->keeper = 24;
  }
}
