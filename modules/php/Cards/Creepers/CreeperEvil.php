<?php
namespace StarFluxx\Cards\Creepers;

use StarFluxx\Game\Utils;

class CreeperEvil extends CreeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Evil");
    $this->subtitle = clienttranslate("Place Immediately + Redraw");
    $this->description = clienttranslate(
      "If you have Keepers in play, you must choose one to attach this to. Both cards stay together until discarded. You cannot win if you have this, unless the Goal says otherwise."
    );

    $this->help = clienttranslate(
      ""
    );
  }

  public function preventsWinForGoal($goalCard)
  {
    $requiredForGoals = [107, 109, 110, 113, 125];
    // Evil is required to win with these specific goals:
    // GoalThePowerOfTheDarkSide (107)
    // GoalEvilComputer (109)
    // GoalEvilBrainParasites (110)
    // GoalImperialStarDestroyer (113)
    // GoalTheRobotsHaveTurnedAgainstUs (125)
    if (in_array($goalCard->getUniqueId(), $requiredForGoals)) {
      return false;
    }

    return parent::preventsWinForGoal($goalCard);
  }

  public $interactionNeeded = "keeperSelectionSelf";

  public function onCheckResolveKeepersAndCreepers($lastPlayedCard)
  {
    // @TODO: when placed or any other time as soon as owner also has at least 1 Keeper of any type,
    // let player select the Keeper to attach this to => visualize and always discard together
    return null;
  } 

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    return null;
  }
}
