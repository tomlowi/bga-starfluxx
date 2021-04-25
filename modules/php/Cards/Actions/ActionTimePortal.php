<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionTimePortal extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Trash a Keeper");
    $this->description = clienttranslate(
      "Take a Keeper from in front of any player and put it on the discard pile. <br/> If no one has any Keepers in play, nothing happens when you play this card."
    );

    $this->help = clienttranslate(
      "Select any keeper card in play from any player (including yourself)."
    );
  }

  public $interactionNeeded = "keeperSelectionAny";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $totalKeepersInPlay = count(
      $game->cards->getCardsOfTypeInLocation("keeper", null, "keepers", null)
    );
    if ($totalKeepersInPlay == 0) {
      // no keepers on the table, this action does nothing
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
    $origin_player_id = $card["location_arg"];

    if ($card_type != "keeper" || $card_location != "keepers") {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper card in front of another player"
        )
      );
    }

    // move this keeper to the discard
    $game->cards->playCard($card["id"]);

    $game->notifyAllPlayers(
      "keepersDiscarded",
      clienttranslate('${player_name} trashed <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $game->getActivePlayerName(),
        "card_name" => $card_definition->getName(),
        "cards" => [$card],
        "player_id" => $origin_player_id,
        "discardCount" => $game->cards->countCardInLocation("discard"),
        "creeperCount" => Utils::getPlayerCreeperCount($origin_player_id),
      ]
    );
  }
}
