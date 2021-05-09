<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;
use starfluxx;

class KeeperTheDoctor extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Doctor");
    $this->set = "vertical";

    $this->description = clienttranslate("During your turn, if you have Brain Parasites, you can detach and discard them as long as they aren't attached to this card.");

    $this->brainparasites = 51;
  }

  public function getKeeperType()
  {
    return "brains";
  }

  public $interactionNeeded = null;
  public function canBeUsedInPlayerTurn($player_id)
  {
    $game = Utils::getGame();
    // player must have both this and Malfunction in play
    $doctor_player = Utils::findPlayerWithKeeper($this->uniqueId);
    $brainparasites_player = Utils::findPlayerWithCreeper($this->brainparasites);

    // but Doctor itself must not be infected with BrainParasites
    $parasitesAttached = $game->getGameStateValue("creeperBrainParasitesAttachedTo");
    $doctorInfected = $parasitesAttached == $doctor_player["keeper_card"]["id"];

    return !$doctorInfected && $this->playerHasDoctorAndBrainParasites($player_id, 
      $doctor_player, $brainparasites_player);
  }

  private function playerHasDoctorAndBrainParasites($player_id, $doctor_player, $brainparasites_player)
  {
    return $doctor_player != null 
      && $brainparasites_player != null
      && $doctor_player["player_id"] == $player_id
      && $brainparasites_player["player_id"] == $player_id
      ;
  }

  public function freePlayInPlayerTurn($player_id)
  {
    $game = Utils::getGame();

    $doctor_player = Utils::findPlayerWithKeeper($this->uniqueId);
    $brainparasites_player = Utils::findPlayerWithCreeper($this->brainparasites);

    $playerHasBoth = $this->playerHasDoctorAndBrainParasites($player_id, 
      $doctor_player, $brainparasites_player);

    $parasitesAttached = $game->getGameStateValue("creeperBrainParasitesAttachedTo");
    $doctorInfected = $parasitesAttached == $doctor_player["keeper_card"]["id"];
    
    if (!$playerHasBoth || $doctorInfected)
    {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must have both the (uninfected) Doctor and Brain Parasites in play"
        )
      );
    }

    $player_name = $game->getActivePlayerName();

    $card = $brainparasites_player["creeper_card"];
    $card_definition = $game->getCardDefinitionFor($card);
    // if BrainParasites is attached to something, first detach it
    if ($parasitesAttached > -1) {
      $game->setGameStateValue("creeperBrainParasitesAttachedTo", -1);

      $game->notifyAllPlayers(
        "creeperDetached",
        clienttranslate('${player_name} uses <b>${this_name}</b> to detach <b>${card_name}</b>'),
        [
          "i18n" => ["this_name", "card_name"],
          "player_id" => $player_id,
          "player_name" => $player_name,
          "this_name" => $this->getName(),
          "card_name" => $card_definition->getName(),
          "card" => $card,
          "creeper" => $card["type_arg"],
          "creeperCount" => Utils::getPlayerCreeperCount($player_id),
        ]
      );
    }

    // finally move BrainParasites to discard pile
    $game->cards->playCard($card["id"]);

    $game->notifyAllPlayers(
      "keepersDiscarded",
      clienttranslate('${player_name} discarded <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $player_name,
        "card_name" => $card_definition->getName(),
        "cards" => [$card],
        "player_id" => $player_id,
        "discardCount" => $game->cards->countCardInLocation("discard"),
        "creeperCount" => Utils::getPlayerCreeperCount($player_id),
      ]
    );
  }
}
