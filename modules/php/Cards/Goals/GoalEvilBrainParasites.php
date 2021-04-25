<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalEvilBrainParasites extends GoalTwoCreepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Evil Brain Parasites");
    $this->subtitle = clienttranslate("Evil + Brain Parasites");

    $this->creeper1 = 52;
    $this->creeper2 = 51;
  }
}
