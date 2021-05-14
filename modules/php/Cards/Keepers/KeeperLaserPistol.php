<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;
use starfluxx;

class KeeperLaserPistol extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Laser Pistol");

    $this->description = clienttranslate("Once during your turn, you can discard any Keeper anywhere in play (other than this one) if it has a Creeper attached to it.");
  }

  public function getKeeperType()
  {
    return "equipment";
  }

  public $interactionNeeded = "keeperSelectionAny";

  public function canBeUsedInPlayerTurn($player_id)
  {
    $alreadyUsed = !Utils::playerHasNotYetUsedLaserPistol();
    if ($alreadyUsed) return false;

    $game = Utils::getGame();
    // Laser Pistol can only be used on keepers with attached creepers
    $keeper1 = $game->getGameStateValue("creeperBrainParasitesAttachedTo");
    $keeper2 = $game->getGameStateValue("creeperEvilAttachedTo");
    $keeper3 = $game->getGameStateValue("creeperMalfunctionAttachedTo");    
       
    return $keeper1 > -1 || $keeper2 > -1 || $keeper3 > -1;
  }

  public function resolvedBy($player_id, $args) 
  {
    $game = Utils::getGame();
    $game->setGameStateValue("playerTurnUsedLaserPistol", 1);

    $card = $args["card"];

    $card_type = $card["type"];
    $card_unique = $card["type_arg"];
    $card_location = $card["location"];
    $origin_player_id = $card["location_arg"];

    // Laser Pistol can only be used on keepers with attached creepers
    $keeper1 = $game->getGameStateValue("creeperBrainParasitesAttachedTo");
    $keeper2 = $game->getGameStateValue("creeperEvilAttachedTo");
    $keeper3 = $game->getGameStateValue("creeperMalfunctionAttachedTo");

    $hasCreeper = ($keeper1 == $card["id"]) 
      || ($keeper2 == $card["id"]) || ($keeper3 == $card["id"]);

    if ($card_type != "keeper" || $card_location != "keepers" || !$hasCreeper) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper card in front of any player, with a creeper attached"
        )
      );
    }

    $notificationMsg = clienttranslate(
      '${player_name} uses <b>${trigger_name}</b> on <b>${card_name}</b> from ${player_name2}'
    );

    Utils::discardKeeperFromPlay($player_id, $card,
      $origin_player_id, $this->getName(), $notificationMsg);    
  }
}
