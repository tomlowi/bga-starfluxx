<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;
use starfluxx;

class KeeperLaserSword extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Laser Sword");

    $this->description = clienttranslate("Once during your turn, you can discard any Keeper you have in play (other than this one) if it has a Creeper attached to it.");
  }

  public function getKeeperType()
  {
    return "equipment";
  }

  public $interactionNeeded = "keeperSelectionSelf";

  private function checkKeeperInPlayWithThisPlayer($keeper_card_id, $player_id)
  {
    $game = Utils::getGame();
    $card = $game->cards->getCard($keeper_card_id);
    return $card["location"] == "keepers" && $card["location_arg"] == $player_id;
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    $alreadyUsed = !Utils::playerHasNotYetUsedLaserSword();
    if ($alreadyUsed) return false;

    $game = Utils::getGame();
    $playerHasKeeperWithCreeper = false;
    // Laser Sword can only be used on own keepers with attached creepers
    $keeper1 = $game->getGameStateValue("creeperBrainParasitesAttachedTo");
    if (!$playerHasKeeperWithCreeper && $keeper1 > -1 && $keeper1 != $this->getCardId()) {
      $playerHasKeeperWithCreeper 
        = $this->checkKeeperInPlayWithThisPlayer($keeper1, $player_id);            
    }
    
    $keeper2 = $game->getGameStateValue("creeperEvilAttachedTo");
    if (!$playerHasKeeperWithCreeper && $keeper2 > -1 && $keeper2 != $this->getCardId()) {
      $playerHasKeeperWithCreeper 
        = $this->checkKeeperInPlayWithThisPlayer($keeper2, $player_id);            
    }

    $keeper3 = $game->getGameStateValue("creeperMalfunctionAttachedTo");
    if (!$playerHasKeeperWithCreeper && $keeper3 > -1 && $keeper3 != $this->getCardId()) {
      $playerHasKeeperWithCreeper 
        = $this->checkKeeperInPlayWithThisPlayer($keeper3, $player_id);            
    }
       
    return $playerHasKeeperWithCreeper;
  }

  public function resolvedBy($player_id, $args) 
  {
    $game = Utils::getGame();
    $game->setGameStateValue("playerTurnUsedLaserSword", 1);

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

    if ($card_type != "keeper" || $card_location != "keepers" 
        || $origin_player_id != $player_id || !$hasCreeper
        || $card["id"] == $this->getCardId()) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper card in front of yourself, with a creeper attached"
        )
      );
    }

    $notificationMsg = clienttranslate(
      '${player_name} uses <b>${trigger_name}</b> on <b>${card_name}</b>'
    );

    Utils::discardKeeperFromPlay($player_id, $card,
      $origin_player_id, $this->getName(), $notificationMsg);    
  }
}
