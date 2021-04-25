<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalWereLostInSpace extends GoalCreeperWithKeeper
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("We're Lost in Space");
    $this->subtitle = clienttranslate("Stars + Malfunction");

    $this->creeper = 53;
    $this->keeper = 14;
  }
}
