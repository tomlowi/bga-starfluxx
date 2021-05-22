<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionTimePortal extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Time Portal");
    $this->description = clienttranslate(
      "Pick up either the discard pile (the Past) or the draw pile (the Future) and choose any non-Creeper card you wish. Leave the order unchanged for the Past, and re-shuffle if you visit the Future. After revealing what you selected, the card goes into your hand and your turn ends immediately."
    );
    
  }

  public $interactionNeeded = null;

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    // @TODO: player can choose to open up discard or draw pile
    // then take a card from it (like basic Let's Do That Again)

    $game->notifyAllPlayers(
      "notImplemented",
      clienttranslate('Sorry, <b>${card_name}</b> not yet implemented'),
      [
        "i18n" => ["card_name"],
        "card_name" => $this->getName(),
      ]
    );

    // special ability when Action = Time Portal is played by owner of Time Traveler
    $keeperTimeTraveler = 21;
    $timetraveler_player = Utils::findPlayerWithKeeper($keeperTimeTraveler);
    if ($timetraveler_player == null || $timetraveler_player["player_id"] != $player_id) {
      return; // nothing else to do, Time Traveler not in play or with other player
    }
    // Time Traveler makes this go back to hand instead of to discard pile
    $card = $game->cards->getCard($this->getCardId());
    $game->cards->moveCard($card["id"], "hand", $player_id);

    // Then we notify players and update the discard pile
    $game->notifyAllPlayers(
      "cardTakenFromDiscard",
      clienttranslate(
        '<b>${card_name}</b> moves back into the hand of the Time Traveler'
      ),
      [
        "i18n" => ["card_name"],
        "card" => $card,
        "card_name" => $this->getName(),
        "discardCount" => $game->cards->countCardInLocation("discard"),
      ]
    );
    $game->notifyPlayer($player_id, "cardsDrawn", "", [
      "cards" => [$card],
    ]);
  }
}
