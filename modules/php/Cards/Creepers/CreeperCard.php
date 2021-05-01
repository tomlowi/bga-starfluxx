<?php
namespace StarFluxx\Cards\Creepers;

use StarFluxx\Cards\Card;
use StarFluxx\Game\Utils;
/*
 * CreeperCard: simple class to handle all creeper cards
 */
class CreeperCard extends Card
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  // Creepers in play globally prevent the player winning with almost all basic Goals
  public function preventsWinForGoal($goalCard)
  {
    return true;
  }

  // Creepers can have special side effects to be triggered on various times
  // All these can return a state transition if further player interaction is needed

  public function onGoalChange()
  {
    return null;
  }

  public function onTurnStart()
  {
    return null;
  }

  public function onCheckResolveKeepersAndCreepers($lastPlayedCard)
  {
    if ($this->interactionNeeded != null) {
      return "resolveCreeper";
    }
    return null;
  }

  // Indicates which interaction is expected by this Creeper
  // null indicated that this action can be handled without client-side interaction
  public $interactionNeeded = null;

  public function resolveArgs()
  {
    return [];
  }
}
