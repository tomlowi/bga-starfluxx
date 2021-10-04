<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Cards\Card;
use StarFluxx\Game\Utils;
/*
 * KeeperCard: simple class to handle all keeper cards
 */
class KeeperCard extends Card
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);    
  }

  public function getKeeperType()
  {
    return "";
  }

  // Indicates this Keeper has an effect can be used during client-side player turns
  public function canBeUsedInPlayerTurn($player_id)
  {
    return false;
  }

  // Indicates which interaction is expected by this Free Rule
  // null indicated that this can be handled without client-side interaction
  public $interactionNeeded = null;

  // Implements the immediate effect when this rule is used in player turn
  public function freePlayInPlayerTurn($player_id)
  {
    if ($this->interactionNeeded != null) {
      Utils::getGame()->setGameStateValue(
        "freeRuleToResolve",
        $this->getCardId()
      );
      return "resolveFreeRule";
    }
    return null;
  }

  public function resolveArgs()
  {
    return [];
  }

  public function onTurnEnd()
  {
    return null;
  }

  public function onMalfunction()
  {
    return null;
  }

  public function onMoveAwayFromPlayer($origin_player_id)
  {
    return null;
  }

  protected function findPlayerWithThisKeeper()
  {
    return Utils::findPlayerWithKeeper($this->uniqueId);
  }  
}
