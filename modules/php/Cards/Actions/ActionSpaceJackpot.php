<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionSpaceJackpot extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Space Jackpot!");
    $this->description = clienttranslate("Draw 5 extra cards, add them to your hand, then discard 2 cards.");

    $this->help = clienttranslate(
      "Select 2 cards from your hand to discard."
    );
  }

  public $interactionNeeded = "handCardsSelection";

  public function immediateEffectOnPlay($player_id)
  {
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $extraCards = 5 + $addInflation;
    Utils::getGame()->performDrawCards($player_id, $extraCards, true);

    // basic jackpot (draw 3) gets extended to (draw 5, then discard 2)
    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $cards = $args["cards"];
    // validate 2 cards get discarded
    if (count($cards) != 2) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must discard exactly 2 cards from your hand"
        )
      );
    }
    // validate all cards are indeed in hand of player
    foreach ($cards as $card) {
      if (
        $card["location"] != "hand" ||
        $card["location_arg"] != $player_id
      ) {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate(
            "You must discard exactly 2 cards from your hand"
          )
        );
      }
    }
    // discard the selected cards from hand
    foreach ($cards as $card) {
      $game->cards->playCard($card["id"]);
    }

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $player_id,
      "cards" => $cards,
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $player_id),
    ]);
  }
}
