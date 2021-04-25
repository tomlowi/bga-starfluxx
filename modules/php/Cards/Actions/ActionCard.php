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

  // Indicates which interaction is expected by this Action
  // null indicated that this action  can be handled without client-side interaction
  public $interactionNeeded = null;

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
