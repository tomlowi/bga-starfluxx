<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionDraw3Play2 extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Draw 3, Play 2 of Them");
    $this->description = clienttranslate(
      "Set your hand aside. Draw 3 cards and play 2 of them. Discard the last card, then pick up your hand and continue with your turn. This card, and all cards played because of it, are counted as a single play."
    );
  }

  public $interactionNeeded = null;

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $addInflation = Utils::getActiveInflation() ? 1 : 0;

    $tmpHandActive = Utils::getActiveTempHand();
    $tmpHandNext = $tmpHandActive + 1;

    $tmpHandLocation = "tmpHand" . $tmpHandNext;
    // Draw 3 for temp hand
    $tmpCards = $game->performDrawCards(
      $player_id,
      3 + $addInflation,
      true, // $postponeCreeperResolve
      true
    ); // $temporaryDraw
    $tmpCardIds = array_column($tmpCards, "id");
    // Must Play 2 of them
    $game->setGameStateValue($tmpHandLocation . "ToPlay", 2 + $addInflation);
    $game->setGameStateValue($tmpHandLocation . "Card", $this->getUniqueId());

    // move cards to temporary hand location
    $game->cards->moveCards($tmpCardIds, $tmpHandLocation, $player_id);

    // done: next play run will detect temp hand active
  }
}
