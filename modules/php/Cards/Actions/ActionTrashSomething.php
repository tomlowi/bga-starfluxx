<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionTrashSomething extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->set = "creeperpack";
    $this->name = clienttranslate("Trash Something");
    $this->description = clienttranslate(
      "Take your choice of any Keeper or Creeper from in front of any player and put it on the discard pile. If no one has any Keepers or Creepers, nothing happens when you play this card."
    );

    $this->help = clienttranslate(
      "Select any keeper or creeper card in play from any player (including yourself)."
    );
  }

  public $interactionNeeded = "keeperSelectionAny";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $keepersInPlay = $game->cards->countCardInLocation("keepers");
    if ($keepersInPlay == 0) {
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

    $card_location = $card["location"];
    $origin_player_id = $card["location_arg"];

    if ($card_location != "keepers") {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper or creeper card in front of any player"
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
