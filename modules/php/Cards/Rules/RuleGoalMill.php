<?php
namespace StarFluxx\Cards\Rules;

use StarFluxx\Game\Utils;
use starfluxx;

class RuleGoalMill extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Goal Mill");
    $this->subtitle = clienttranslate("Free Action");
    $this->description = clienttranslate(
      "Once during your turn, discard as many of your Goal cards as you choose, then draw that many cards."
    );

    $this->help =
      clienttranslate(
        "Select any number of goal cards in your hand to discard. Click the button when finished."
      );
  }

  public $interactionNeeded = "handCardsSelection";

  public function canBeUsedInPlayerTurn($player_id)
  {
    $alreadyUsed = !Utils::playerHasNotYetUsedGoalMill();
    if ($alreadyUsed) {
      return false;
    }

    $game = Utils::getGame();
    $goalCardsInHand = $game->cards->getCardsOfTypeInLocation(
      "goal",
      null,
      "hand",
      $player_id
    );

    return count($goalCardsInHand) > 0;
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
    $game->setGameStateValue("playerTurnUsedGoalMill", 1);
    return parent::freePlayInPlayerTurn($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    // validate all cards are goals in hand of player
    $cards = $args["cards"];
    foreach ($cards as $card) {
      if (
        $card["location"] != "hand" ||
        $card["location_arg"] != $player_id ||
        $card["type"] != "goal"
      ) {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate(
            "You can only discard Goals from your hand for the Goal Mill"
          )
        );
      }
    }
    // discard the selected goals from hand
    foreach ($cards as $card) {
      $game->cards->playCard($card["id"]);
    }

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $player_id,
      "cards" => $cards,
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $player_id),
    ]);
    // draw equal number of cards

    $drawCount = count($cards);
    $game->performDrawCards($player_id, $drawCount);
  }
}
