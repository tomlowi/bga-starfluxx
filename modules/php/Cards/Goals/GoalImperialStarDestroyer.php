<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalImperialStarDestroyer extends GoalCreeperWithKeeper
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Imperial Star Destroyer");
    $this->subtitle = clienttranslate("Starship + Evil");

    $this->creeper = 52;
    $this->keeper = 15;
  }
}
