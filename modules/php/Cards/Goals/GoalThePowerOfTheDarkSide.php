<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalThePowerOfTheDarkSide extends GoalCreeperWithKeeper
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The Power of the Dark Side");
    $this->subtitle = clienttranslate("Evil + Unseen Force");

    $this->creeper = 52;
    $this->keeper = 23;
  }
}
