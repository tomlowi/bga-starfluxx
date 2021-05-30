<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionThatsMine extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("That's Mine!");
    $this->description = clienttranslate(
      "<b>Out of turn:</b> When another player plays a Keeper, it goes in front of you instead of them, possibly preventing their victory. <b>During your turn:</b> Steal another player's Keeper and put it in front of you.<br>This card can also cancel another Surprise."
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
