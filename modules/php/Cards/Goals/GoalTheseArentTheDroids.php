<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalTheseArentTheDroids extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("These Aren't the Droids...");
    $this->subtitle = clienttranslate("Unseen Force + The Robot");

    $this->keeper1 = 23;
    $this->keeper2 = 24;
  }
}
