<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionBrainTransference extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Brain Transference");
    $this->description = clienttranslate(
      "You and a player of your choice switch seats at the table (leaving your hands). You each take over the other player's entire position in the game, as if you were in that position all along. Your turn ends immediately. The person next in turn order from your original seat goes next unless that's you, in which case the player after your new seat goes next."
    );

  }

  public $interactionNeeded = null;


  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    // @TODO: something like trading hands and all keepers
    // but in theory we should really adapt the player order in the middle of the game
    // to check if this is possible on BGA

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
