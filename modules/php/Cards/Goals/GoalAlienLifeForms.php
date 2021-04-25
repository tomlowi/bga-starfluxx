<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalAlienLifeForms extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Alien Life Forms");
    $this->subtitle = clienttranslate("Any 2 of: Energy Being, Bug-Eyed Monster, or Cute Fuzzy Alien Creature");

    $this->alien1 = 2;
    $this->alien2 = 5;
    $this->alien3 = 7;
  }

  public function goalReachedByPlayer()
  {
    $i = 0;
    $winner_id = null;
    if ( $winner_id == null) {
      $winner_id = $this->checkTwoKeepersWin($this->alien1, $this->alien2);
    }
    if ( $winner_id == null) {
      $winner_id = $this->checkTwoKeepersWin($this->alien1, $this->alien3);
    }
    if ( $winner_id == null) {
      $winner_id = $this->checkTwoKeepersWin($this->alien2, $this->alien3);
    }

    return $winner_id;
  }
}
