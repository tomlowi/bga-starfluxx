<?php
namespace StarFluxx\Cards\Rules;

use StarFluxx\Game\Utils;

class RuleGetOnWithIt extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Get On With It!");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Before your final play, if you are not empty handed, you may discard your entire hand and draw 3 cards. Your turn then ends immediately."
    );
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    $game = Utils::getGame();
    return $game->cards->countCardInLocation("hand", $player_id) > 0 &&
      Utils::calculateCardsLeftToPlayFor($player_id) > 0;
  }

  public function immediateEffectOnPlay($player)
  {
    // nothing
  }

  public function immediateEffectOnDiscard($player)
  {
    // nothing
  }

  public function freePlayInPlayerTurn($player_id)
  {
    $game = Utils::getGame();
    // Discard entire hand
    $cards = $game->cards->getCardsInLocation("hand", $player_id);
    foreach ($cards as $card_id => $card) {
      $game->cards->playCard($card_id);
    }

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $player_id,
      "cards" => $cards,
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $player_id),
    ]);
    // Draw 3 cards (+ inflation)
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $drawCount = 3 + $addInflation;
    $game->performDrawCards($player_id, $drawCount);

    // Force end of turn
    return "endOfTurn";
  }
}
