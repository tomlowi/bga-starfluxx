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
  }
}
