<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleCardFactory;
use starfluxx;

class ActionBeamUsUp extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Beam Us Up!");
    $this->description = clienttranslate(
      "All beings in play are returned to the hands of their owners, unless someone has the Teleport Chamber in play, in which case that player takes all beings in play and adds them to their hand."
    );

  }

  public $interactionNeeded = null;

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    
    // @TODO: teleport all beings

    $game->notifyAllPlayers(
      "notImplemented",
      clienttranslate('Sorry, <b>${card_name}</b> not yet implemented'),
      [
        "i18n" => ["card_name"],
        "card_name" => $this->getName(),
      ]
    );

    return parent::immediateEffectOnPlay($player_id);
  }

}
