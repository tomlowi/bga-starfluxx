<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionCanceledPlans extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Canceled Plans");
    $this->description = clienttranslate(
      "<b>Out of turn:</b> Discard a Goal another player just played, thus preventing possible victory. <b>During your turn:</b> Discard the current Goal(s). Also, all other players must discard a Goal, or a random card, from their hands.<br>This card can also cancel another Surprise."
    );

    // https://faq.looneylabs.com/question/188
    // Note that "discard a Goal, or a random card" means that either the player chooses
    // a specific Goal card to discard, or a random card is discarded - *not* chosen by the player then

    $this->help = clienttranslate(
      "Select any goal card to discard from your hand, or select nothing and get a random card discarded."
    );
  }

  public function getActionType()
  {
    return "surprise";
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    // discard the current goal(s)
    $goalCards = $game->cards->getCardsInLocation("goals");
    if (!empty($goalCards)) {
      foreach ($goalCards as $card_id => $card) {
        $game->cards->playCard($card["id"]);
      }

      $game->notifyAllPlayers("goalsDiscarded", "", [
        "cards" => $goalCards,
        "discardCount" => $game->cards->countCardInLocation("discard"),
      ]);
    }    

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
    // if a card was selected, validate it is a goal card in hand of player and discard
    // if not, select any random card from player hand and discard that
    $card = $args["card"];

    if ($card != null && (
      $card["type"] != "goal" ||
      $card["location"] != "hand" ||
      $card["location_arg"] != $player_id
      )
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must discard a goal card from your hand"
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

  public function outOfTurnCounterPlay($surpriseTargetId)
  {
    $game = Utils::getGame();

    $surpriseCounterId = $this->getCardId();

    $targetCard = $game->cards->getCard($surpriseTargetId);
    $targetPlayerId = $targetCard["location_arg"];
    $surpriseCard = $game->cards->getCard($surpriseCounterId);
    $surprisePlayerId = $surpriseCard["location_arg"];
    $game->cards->playCard($surpriseTargetId);
    $game->cards->playCard($surpriseCounterId);

    // Cancel the Goal played => discard it, and discard this card    
    $discardCount =$game->cards->countCardInLocation("discard");
    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $targetPlayerId,
      "cards" => [$targetCard],
      "discardCount" => $discardCount,
      "handCount" => $game->cards->countCardInLocation("hand", $targetPlayerId),
    ]);
    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $surprisePlayerId,
      "cards" => [$surpriseCard],
      "discardCount" => $discardCount,
      "handCount" => $game->cards->countCardInLocation("hand", $surprisePlayerId),
    ]); 
  }
}
