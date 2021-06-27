<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionDraw2AndUseEm extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Draw 2 and Use ‘Em");
    $this->description = clienttranslate(
      "Set your hand aside. Draw 2 cards, play them in any order you choose, then pick up your hand and continue with your turn. This card, and all cards played because of it, are counted as a single play."
    );
  }

  public $interactionNeeded = null;

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $addInflation = Utils::getActiveInflation() ? 1 : 0;

    $cardsToDraw = 2 + $addInflation;
    // there must be enough available cards to draw, otherwise this action can't do anything
    // and remember we can't redraw this card itself (as it is still being resolved)
    $countAvailable = ($game->cards->countCardInLocation("discard") - 1)
      + $game->cards->countCardInLocation("deck");
    if ($countAvailable < $cardsToDraw) {
      $game->notifyAllPlayers(
        "actionIgnored",
        clienttranslate(
          'Not enough available cards to draw for <b>${card_name}</b>'
        ), [
          "i18n" => ["card_name"],
          "player_id" => $player_id,
          "card_name" => $this->getName(),
          ]
      );
      return null;
    }

    $tmpHandActive = Utils::getActiveTempHand();
    $tmpHandNext = $tmpHandActive + 1;

    $tmpHandLocation = "tmpHand" . $tmpHandNext;
    // Draw 2 for temp hand

    // make sure we can't draw back this card itself (after reshuffle if deck would be empty)
    $game->cards->moveCard($this->getCardId(), "side", $player_id);

    $tmpCards = $game->performDrawCards(
      $player_id,
      $cardsToDraw,
      true, // $postponeCreeperResolve
      true
    ); // $temporaryDraw
    $tmpCardIds = array_column($tmpCards, "id");
    // Must Use 'Em both
    $game->setGameStateValue($tmpHandLocation . "ToPlay", 2 + $addInflation);
    $game->setGameStateValue($tmpHandLocation . "Card", $this->getUniqueId());

    // move cards to temporary hand location
    $game->cards->moveCards($tmpCardIds, $tmpHandLocation, $player_id);

    // move this card itself back to the discard pile
    $game->cards->playCard($this->getCardId());

    // done: next play run will detect temp hand active
  }
}
