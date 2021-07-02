<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionCanceledPlans extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Canceled Plans");
    $this->description = clienttranslate(
      "<b>Out of turn:</b> Discard a Goal another player just played, thus preventing possible victory. <b>During your turn:</b> Discard the current Goal(s). Also, all other players must discard a Goal, or a random card, from their hands.<br>This card can also cancel another Surprise."
    );

    // https://faq.looneylabs.com/question/188
    // Note that "discard a Goal, or a random card" means that either the player chooses
    // a specific Goal card to discard, or a random card is discarded - *not* chosen by the player then
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
