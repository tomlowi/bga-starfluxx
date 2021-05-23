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

  // @TODO in general: if a Creeper and Keeper are attached,
  // whenever one of them gets stolen, moved, taken, ... they should always move together (and stay attached)
  // whenever one gets discarded, both should be discarded together (and get detached in the discard pile)
  // this needs to be checked specifically when keepers are discarded for the keeper limit!

  protected function findPlayerWithThisCreeper()
  {
    return Utils::findPlayerWithCreeper($this->uniqueId);
  }

  protected function findKeepersOfType($player_id, $keeper_type) {
    $game = Utils::getGame();
    $keeper_cards = $game->cards->getCardsInLocation("keepers", $player_id);

    $keepersMatching = [];
    foreach ($keeper_cards as $card_id => $card) {
      if ($card["type"] == "creeper")
        continue;
      $keeper = $game->getCardDefinitionFor($card);
      if ($keeper_type == null || $keeper->getKeeperType() == $keeper_type) {
        $keepersMatching[] = $keeper;
      }      
    }

    return $keepersMatching;
  }
}
