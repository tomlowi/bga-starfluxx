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

    $this->keeperHolograph = 8;
    $this->expendable = 12;
    $this->crew1 = 17;
    $this->crew2 = 18;
    $this->crew3 = 19;
    $this->crew4 = 20;
  }

  public function goalReachedByPlayer()
  {
    $game = Utils::getGame();
    $cards = $game->cards;

    $tester_id = null;
    // which player has the expendable crewman?
    $expendable_keeper_card = array_values(
      $cards->getCardsOfType("keeper", $this->expendable)
    )[0];

    if ($expendable_keeper_card["location"] == "keepers") {
      $tester_id = $expendable_keeper_card["location_arg"];
    }
    // if nobody has it, or there is a creeper on the ExpendableCrewman, nobody can win this
    // (because that creeper is holographed together so prevents win)
    if ($tester_id == null || Utils::countNumberOfCreeperAttached($expendable_keeper_card["id"]) > 0) {
      return null;
    }

    // Exceptionally, the Holographic projection can let player win with Keeper from someone else
    // But only in their turn, so they must be the active player!
    $active_player_id = $game->getActivePlayerId();
    $holograph_player_id = null;
    $player_with_holograph = Utils::findPlayerWithKeeper($this->keeperHolograph);
    if ($player_with_holograph != null && $player_with_holograph["player_id"] == $active_player_id) {
      if (!Utils::checkForMalfunction($player_with_holograph["keeper_card"]["id"]))
      {
        $holograph_player_id = $player_with_holograph["player_id"];
      }      
    }
    // https://faq.looneylabs.com/fluxx-games/star-fluxx#1265
    // Active player with holographic projector takes precedence over other player that has both keepers!

    $winner_id = null;
    // so, if player with holograph needs to project the Expendable Crewman, it needs 2 other keepers for real
    if ($holograph_player_id != null && $tester_id != $holograph_player_id
      && $this->checkPlayerHas2OtherCrewMembers($holograph_player_id, false))
    {
      $winner_id = $holograph_player_id;
    }
    // but if holograph player has the Expendable Crewman, they can project 1 of the other 2 keepers
    else if ($holograph_player_id != null && $tester_id == $holograph_player_id
      && $this->checkPlayerHas2OtherCrewMembers($holograph_player_id, true))
    {
      $winner_id = $holograph_player_id;
    }
    // else, no holographs, any player with a full Landing Party just wins
    else if ($this->checkPlayerHas2OtherCrewMembers($tester_id, false))
    {
      $winner_id = $tester_id;
    }
   
    return $winner_id;
  }

  private function checkPlayerHas2OtherCrewMembers($tester_id, $allow_holograph)
  {
    $winner_id = null;
    // does this player also have 2 other crew members?
    $check_id = $this->checkTwoKeepersWin($this->crew1, $this->crew2, $allow_holograph);
    if ($check_id != null && $check_id == $tester_id) {
      $winner_id = $tester_id;
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew1, $this->crew3, $allow_holograph);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew1, $this->crew4, $allow_holograph);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew2, $this->crew3, $allow_holograph);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew2, $this->crew4, $allow_holograph);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }
    if ($winner_id == null) {
      $check_id = $this->checkTwoKeepersWin($this->crew3, $this->crew4, $allow_holograph);
      if ($check_id != null && $check_id == $tester_id) {
        $winner_id = $tester_id;
      }
    }

    return $winner_id != null;
  }
}
