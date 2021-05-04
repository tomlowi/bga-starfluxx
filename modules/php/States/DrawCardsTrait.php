<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleInflation;

trait DrawCardsTrait
{
  public function st_drawCards()
  {
    $game = Utils::getGame();
    $player_id = $game->getActivePlayerId();
    $cards = $game->cards;

    $cardsInHand = $cards->countCardInLocation("hand", $player_id);

    $addInflation = Utils::getActiveInflation() ? 1 : 0;

    $drawRule = $game->getGameStateValue("drawRule");
    // Check for other draw bonuses
    if ($addInflation > 0) {
      RuleInflation::notifyActiveFor($player_id);
    }

    $cardsToDraw = $drawRule + $addInflation;
    // PlayAllBut1: If you started with no cards in your hand and only drew 1, draw an extra card.
    // => don't apply inflation on this
    if ($cardsInHand == 0 && $cardsToDraw == 1) {
      $playRule = $game->getGameStateValue("playRule");
      if ($playRule == -1) {
        $cardsToDraw += 1;
      }      
    }    

    // entering this state, so this player can always draw for current draw rule
    // postpone creepers to be resolved until after all cards drawn
    $game->performDrawCards(
      $player_id,
      $cardsToDraw,
      true
    );
    $game->setGameStateValue("drawnCards", $drawRule);

    // count statistics start of turn for this player
    self::incStat(1, "turns_number", $player_id);

    // move to state where player is allowed to start playing cards
    $game->gamestate->nextstate("cardsDrawn");

    // check if any creepers must be resolved because of the cards just drawn
    // (e.g. player had Peace on the table and has just drawn War)
    if ($game->checkCreeperResolveNeeded(null)) {
      return;
    }
  }
}
