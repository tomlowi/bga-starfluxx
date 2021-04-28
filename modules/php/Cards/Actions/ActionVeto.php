<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionVeto extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Veto!");
    $this->description = clienttranslate(
      "<b>Out of turn:</b> Discard a New Rule another player just played, thus preventing it from ever taking effect. <b>During your turn:</b> Discard your choice of up to 2 New Rules currently in play.<br>This card can also cancel another Surprise."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    // @TODO: be able to use this as a normal Action, but also anywhere out of turn?

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
