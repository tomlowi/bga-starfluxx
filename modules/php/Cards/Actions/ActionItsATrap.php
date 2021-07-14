<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;

class ActionItsATrap extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("It's a Trap!");
    $this->description = clienttranslate(
      "<b>Out of turn:</b> Cancel any single game action in which another player is stealing a Keeper you have on the table, and instead you steal one of their Keepers. <b>During your turn:</b> All other players must choose a card from their hands to discard, while you draw 2.<br>This card can also cancel another Surprise."
    );

    $this->help = clienttranslate(
      "Select any card to discard from your hand, or select nothing and get a random card discarded."
    );
  }

  public function getActionType()
  {
    return "surprise";
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    // Draw 2 extra cards
    $extraCards = 2;
    // make sure we can't draw back this card itself (after reshuffle if deck would be empty)
    $game->cards->moveCard($this->getCardId(), "side", $player_id);

    $game->performDrawCards($player_id, $extraCards, true);

    // move this card itself back to the discard pile
    $game->cards->playCard($this->getCardId());
        

    // force other players to react
    Utils::getGame()->setGameStateValue(
      "actionToResolve",
      $this->getCardId()
    );
    return "resolveActionByOthers";
  }

  public $interactionNeeded = "handCardOptionalSelection";

  public function resolvedByOther($player_id, $args)
  {
    // if a card was selected, validate it is any card in hand of player and discard
    // if not, select any random card from player hand and discard that
    $card = $args["card"];

    if ($card != null && (
      $card["location"] != "hand" ||
      $card["location_arg"] != $player_id
      )
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must discard any card from your hand"
        )
      );
    }

    $game = Utils::getGame();

    if ($card == null) {
      $handCards = $game->cards->getCardsInLocation("hand", $player_id);
      $handCardsCount = count($handCards);
      if ($handCardsCount > 0) {
        $i = bga_rand(0, $handCardsCount - 1);
        $card = array_values($handCards)[$i];
      }
    }
    // no hand cards > nothing to discard
    if ($card == null) {
      return;
    }
    
    // discard the selected card from hand
    $game->cards->playCard($card["id"]);

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $player_id,
      "cards" => [$card],
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $player_id),
    ]);
  }

}
