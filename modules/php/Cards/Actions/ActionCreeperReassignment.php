<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionCreeperReassignment extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Creeper Reassignment");
    $this->description = clienttranslate(
      "Take any one Creeper that is currently in play and move it to be in front of any other player. If it's currently attached to a Keeper, detach it before moving the Creeper. You must attach it to an appropriate Keeper (if possible) after moving it."
    );

    $this->help = clienttranslate(
      "First select any creeper card in play, then choose the player it should move to."
    );
  }

  public $interactionNeeded = "keeperAndPlayerSelectionAny";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $creeperCards = $game->cards->getCardsOfTypeInLocation(
      "creeper",
      null,
      "keepers",
      null
    );
    if (count($creeperCards) == 0) {
      // no creepers on the table for any player, this action does nothing
      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $card = $args["card"];
    $selected_player_id = $args["selected_player_id"];

    $card_definition = $game->getCardDefinitionFor($card);

    $card_type = $card["type"];
    $card_location = $card["location"];
    $other_player_id = $card["location_arg"];

    if ($card_location != "keepers" || $card_type != "creeper") {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate("You must select a creeper card in play")
      );
    }

    if ($selected_player_id == $other_player_id) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must move the creeper to a player different from the current owner"
        )
      );
    }

    // detach the creeper first (if it is attached)
    $card_definition->detach();

    $game->notifyAllPlayers(
      "creeperDetached", '',
      [
        "creeper" => $card["type_arg"],
      ]
    );

    // move this creeper to the selected player
    $notificationMsg = clienttranslate(
      '${player_name} moved <b>${card_name}</b> from ${player_name2} to ${player_name3}'
    );
    Utils::moveKeeperToPlayer($player_id, $card,
      $other_player_id, $selected_player_id, $notificationMsg); 

  }
}
