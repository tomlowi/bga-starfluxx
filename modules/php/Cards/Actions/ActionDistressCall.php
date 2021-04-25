<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;

class ActionDistressCall extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Discard and Draw");
    $this->description = clienttranslate(
      "Discard your entire hand, then draw as many cards as you discarded. Do not count this card when determining how many cards to draw."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $cards = $game->cards->getCardsInLocation("hand", $player_id);

    // discard all cards
    foreach ($cards as $card_id => $card) {
      $game->cards->playCard($card_id);
    }

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $player_id,
      "cards" => $cards,
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $player_id),
    ]);

    // draw equal nr of new cards
    $game->performDrawCards($player_id, count($cards));
  }
}
