<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Cards\Card;
use StarFluxx\Game\Utils;
/*
 * ActionCard: base class to handle actions cards
 */
class ActionCard extends Card
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  public function getActionType()
  {
    return null;
  }

  // Indicates which interaction is expected by this Action
  // null indicated that this action can be handled without client-side interaction
  public $interactionNeeded = null;
  // Some Action cards also need all other players to do some client interaction
  public $interactionOther = null;

  // Implements the immediate effect when this action is played
  public function immediateEffectOnPlay($player_id)
  {
    if ($this->interactionNeeded != null) {
      Utils::getGame()->setGameStateValue(
        "actionToResolve",
        $this->getCardId()
      );
      return "resolveActionCard";
    }
    return null;
  }

  public function resolveArgs()
  {
    return [];
  }
}
