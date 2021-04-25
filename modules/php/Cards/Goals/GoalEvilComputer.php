<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalEvilComputer extends GoalCreeperWithKeeper
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("That Computer Controls the Life Support System!");
    $this->subtitle = clienttranslate("Evil + The Computer");

    $this->creeper = 52;
    $this->keeper = 3;
  }
}
