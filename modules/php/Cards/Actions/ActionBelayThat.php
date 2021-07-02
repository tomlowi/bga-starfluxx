<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionBelayThat extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Belay That!");
    $this->description = clienttranslate(
      "<b>Out of turn:</b> Cancel an Action another player just played. <b>During your turn:</b> All other players must discard one Action, or a random card, from their hands.<br>This card can also cancel another Surprise."
    );

    // https://faq.looneylabs.com/question/188
    // Note that "discard one Action, or a random card" means that either the player chooses
    // a specific Action card to discard, or a random card is discarded - *not* chosen by the player then
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
