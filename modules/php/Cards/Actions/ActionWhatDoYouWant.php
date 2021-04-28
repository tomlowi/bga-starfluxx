<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionWhatDoYouWant extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("What Do You Want?");
    $this->description = clienttranslate(
      "Remove any card you want from the discard pile. If the card is a... <b>Rule, Action or Surprise:</b> Play the card immediately. <b>Keeper or Goal</b>: Reveal it and add it to your hand. Your turn ends immediately. <b>Creeper</b>: Give it to another player. You choose what to attach it to if applicable."
    );
    
  }

  public $interactionNeeded = null;

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    // @TODO: take card from the discard pile
    // then play it, or add to hand, or give creeper to someone else

    $game->notifyAllPlayers(
      "notImplemented",
      clienttranslate('Sorry, <b>${card_name}</b> not yet implemented'),
      [
        "i18n" => ["card_name"],
        "card_name" => $this->getName(),
      ]
    );
  }
}
