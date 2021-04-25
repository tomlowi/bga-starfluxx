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
      "Steal a Keeper from in front of another player, and add it to your collection of Keepers on the table."
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
    $game->cards->moveCard($card["id"], "keepers", $player_id);

    $players = $game->loadPlayersBasicInfos();
    $other_player_name = $players[$other_player_id]["player_name"];

    $game->notifyAllPlayers(
      "keepersMoved",
      clienttranslate(
        '${player_name} stole <b>${card_name}</b> from ${player_name2}'
      ),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "player_name2" => $other_player_name,
        "card_name" => $card_definition->getName(),
        "destination_player_id" => $player_id,
        "origin_player_id" => $other_player_id,
        "cards" => [$card],
        "destination_creeperCount" => Utils::getPlayerCreeperCount($player_id),
        "origin_creeperCount" => Utils::getPlayerCreeperCount($other_player_id),
      ]
    );
  }
}
