<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Cards\Card;
use StarFluxx\Cards\Creepers\CreeperCardFactory;
use StarFluxx\Game\Utils;
/*
 * GoalCard: base class to handle new goal cards
 */
class GoalCard extends Card
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  /* check if this goal is reached by a single player, and return the winner player id */
  public function goalReachedByPlayer()
  {
    return null;
  }

  public function isWinPreventedByCreepers($player_id, $goalCard)
  {
    $game = Utils::getGame();
    // check all creepers in play for this player,
    // probably they prevent winning (except for some specific goals)
    $creeperCards = $game->cards->getCardsOfTypeInLocation(
      "creeper",
      null,
      "keepers",
      $player_id
    );
    foreach ($creeperCards as $card_id => $card) {
      // overrides in specific Creeper cards to allow wins for specific Goals
      $creeperCard = CreeperCardFactory::getCard($card_id, $card["type_arg"]);
      if ($creeperCard->preventsWinForGoal($goalCard)) {
        return true;
      }
    }

    return false;
  }

}
