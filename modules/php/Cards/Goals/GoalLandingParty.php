<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalLandingParty extends GoalTwoKeepers
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Landing Party");
    $this->subtitle = clienttranslate("Expendable Crewman + any 2 of: The Captain, Engineer, Doctor, or Scientist");

    $this->expendable = 12;
    $this->crew1 = 17;
    $this->crew2 = 18;
    $this->crew3 = 19;
    $this->crew4 = 20;
  }

  public function goalReachedByPlayer()
  {    
    $cards = Utils::getGame()->cards;

    $tester_id = null;
    // which player has the expendable crewman?
    $expendable_keeper_card = array_values(
      $cards->getCardsOfType("keeper", $this->expendable)
    )[0];

    if ($expendable_keeper_card["location"] == "keepers") {
      $tester_id = $expendable_keeper_card["location_arg"];
    }

    if ($tester_id == null) {
      return null;
    }

    $winner_id = null;
    // does this player also have 2 other crew members?
    $check_id = $this->checkTwoKeepersWin($this->crew1, $this->crew2);
    if ($check_id != null && $check_id == $tester_id) {
      $winner_id = $tester_id;
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew1, $this->crew3);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew1, $this->crew4);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew2, $this->crew3);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew2, $this->crew4);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew3, $this->crew4);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }

    return $winner_id;
  }
}
