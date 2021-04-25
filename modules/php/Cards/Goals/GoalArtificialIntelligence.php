<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalArtificialIntelligence extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Artificial Intelligence");
    $this->subtitle = clienttranslate("The Computer + The Robot");

    $this->keeper1 = 3;
    $this->keeper2 = 24;
  }
}
