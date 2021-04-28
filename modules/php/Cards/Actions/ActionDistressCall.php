<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;

class ActionDistressCall extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Distress Call");
    $this->description = clienttranslate(
      "All players draw 1 card from the deck. Anyone with a Creeper then draws additional cards until they have drawn a total of 2 cards for each Creeper they possess."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

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
