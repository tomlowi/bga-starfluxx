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
    // if Silver Lining Rule is active, creepers do not prevent winning
    if (Utils::getActiveSilverLining()) {
      return false;
    }

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

  public function isWinPreventedByBakedPotato($player_id, $goalCard)
  {
    // check Baked Potato active
    if (!Utils::getActiveBakedPotato()) {
      return false;
    }

    // check Radioactive Potato in play somewhere else
    $game = Utils::getGame();
    // Baked Potato rule is active: if the Radioactive Potato is in play,
    // the player that satisfies the goal must also have the Potato
    $potato_creeper = 54;
    $potato_creeper_cards = $game->cards->getCardsOfTypeInLocation(
      "creeper",
      $potato_creeper,
      "keepers"
    );

    if (count($potato_creeper_cards) > 0) {
      $potato_player_id = reset($potato_creeper_cards)["location_arg"];
      return $potato_player_id != $player_id;
    }
    // else: the Potato is not in play, so it is not needed to win (see the FAQ)

    return false;
  }
}
