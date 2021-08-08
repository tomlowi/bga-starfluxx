<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionStealAKeeper extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Steal a Keeper");
    $this->description = clienttranslate(
      "Take your choice of any Keeper from in front of another player and put it in front of you."
    );

    $this->help = clienttranslate(
      "Select any keeper card in play from another player."
    );
  }

  public $interactionNeeded = "keeperSelectionOther";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $totalKeepersInPlay = count(
      $game->cards->getCardsOfTypeInLocation("keeper", null, "keepers", null)
    );
    $playersKeepersInPlay = count(
      $game->cards->getCardsOfTypeInLocation(
        "keeper",
        null,
        "keepers",
        $player_id
      )
    );
    if ($totalKeepersInPlay - $playersKeepersInPlay == 0) {
      // no keepers on the table for others, this action does nothing
      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $card = $args["card"];
    $card_definition = $game->getCardDefinitionFor($card);

    $card_type = $card["type"];
    $card_location = $card["location"];
    $other_player_id = $card["location_arg"];

    if (
      $card_type != "keeper" ||
      $card_location != "keepers" ||
      $other_player_id == $player_id
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper card in front of another player"
        )
      );
    }

    // move this keeper to the current player
    $notificationMsg = clienttranslate(
      '${player_name} stole <b>${card_name}</b> from ${player_name2}'
    );
    Utils::moveKeeperToPlayer($player_id, $card,
      $other_player_id, $player_id, $notificationMsg);

    // check if target player could counter this keeper steal
    return Utils::checkCounterTrapForKeeperStolen($other_player_id, $card["id"]);
  }
}
