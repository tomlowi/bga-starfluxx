<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionSonicSledgehammer extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Sonic Sledgehammer");
    $this->description = clienttranslate(
      "All players, including you, must discard a Keeper from the table. You decide which Keeper each player discards. Players with no Keepers in play must discard a random card from their hands. If someone has the Time Traveler on the table, that player discards nothing."
    );
  }

  public $interactionNeeded = null;

  public function immediateEffectOnPlay($player_id)
  {
    // nothing now, needs to go to resolve action state
    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    // @TODO: player should have selected a Keeper from each player,
    // they must all discard it (or random hand card if they had no keepers)

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
