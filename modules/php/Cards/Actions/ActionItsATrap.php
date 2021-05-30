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
  }

  public function getActionType()
  {
    return "surprise";
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
