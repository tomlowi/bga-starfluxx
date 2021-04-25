<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalAlienArtifacts extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Alien Artifacts");
    $this->subtitle = clienttranslate("Energy Crystals + Monolith");

    $this->keeper1 = 6;
    $this->keeper2 = 11;
  }
}
