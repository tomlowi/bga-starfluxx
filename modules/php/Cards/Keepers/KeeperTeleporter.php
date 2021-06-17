<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;
use starfluxx;

class KeeperTeleporter extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Teleporter");

    // see Star Fluxx FAQ: https://faq.looneylabs.com/question/1079
    // Teleporter text should be read as any *other* keeper, so best adapt that here
    $this->description = clienttranslate("Once during your turn, you can move any 1 of your other Keepers to another player.");

    $this->help = clienttranslate(
      "First select one of your keepers (not the Teleporter), then choose the player it should move to."
    );
  }

  public function getKeeperType()
  {
    return "equipment";
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    $alreadyUsed = !Utils::playerHasNotYetUsedTeleporter();
    if ($alreadyUsed) return false;

    if (Utils::checkForMalfunction($this->getCardId()))
      return false;

    $keeperCount = Utils::getPlayerKeeperCount($player_id);
    // players needs to have at least 1 other keeper than the Teleporter
    return $keeperCount > 1;
  }

  public $interactionNeeded = "keeperSelfAndPlayerOtherSelection";

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $game->setGameStateValue("playerTurnUsedTeleporter", 1);

    $card = $args["card"];
    $selected_player_id = $args["selected_player_id"];
    
    $card_type = $card["type"];
    $card_unique = $card["type_arg"];
    $card_location = $card["location"];
    $card_player_id = $card["location_arg"];

    if (
      $card_type != "keeper" ||
      $card_location != "keepers" ||
      $card_player_id != $player_id ||
      $card_unique == $this->getUniqueId()
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select one of your own keepers other than the Teleporter"
        )
      );
    }

    // move this keeper to the active player
    $notificationMsg = clienttranslate(
      '${player_name} teleported <b>${card_name}</b> to ${player_name2}'
    );
    Utils::moveKeeperToPlayer($player_id, $card,
      $player_id, $selected_player_id, $notificationMsg);
  }
}
