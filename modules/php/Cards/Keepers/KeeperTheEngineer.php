<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;
use starfluxx;

class KeeperTheEngineer extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Engineer");
    $this->set = "vertical";

    $this->description = clienttranslate("During your turn, if you have Malfunction, you can detach and discard it.");

    $this->malfunction = 53;
  }

  public function getKeeperType()
  {
    return "brains";
  }

  public $interactionNeeded = null;
  public function canBeUsedInPlayerTurn($player_id)
  {
    // player must have both this and Malfunction in play
    $engineer_player = Utils::findPlayerWithKeeper($this->uniqueId);
    $malfunction_player = Utils::findPlayerWithCreeper($this->malfunction);

    return $this->playerHasEngineerAndMalfunction($player_id, 
      $engineer_player, $malfunction_player);
  }

  private function playerHasEngineerAndMalfunction($player_id, $engineer_player, $malfunction_player)
  {
    return $engineer_player != null 
      && $malfunction_player != null
      && $engineer_player["player_id"] == $player_id
      && $malfunction_player["player_id"] == $player_id
      ;
  }

  public function freePlayInPlayerTurn($player_id)
  {
    $game = Utils::getGame();

    $engineer_player = Utils::findPlayerWithKeeper($this->uniqueId);
    $malfunction_player = Utils::findPlayerWithCreeper($this->malfunction);

    $playerHasBoth = $this->playerHasEngineerAndMalfunction($player_id, 
      $engineer_player, $malfunction_player);
    
    if (!$playerHasBoth)
    {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must have both the Engineer and Malfunction in play"
        )
      );
    }

    $player_name = $game->getActivePlayerName();

    $card = $malfunction_player["creeper_card"];
    $creeper_definition = $game->getCardDefinitionFor($card);
    // if Malfunction is attached to something, first detach it
    $malfunctionAttached = $game->getGameStateValue("creeperMalfunctionAttachedTo");
    if ($malfunctionAttached > -1) {
      $creeper_definition->detach();

      $game->notifyAllPlayers(
        "creeperDetached",
        clienttranslate('${player_name} uses <b>${this_name}</b> to detach <b>${card_name}</b>'),
        [
          "i18n" => ["this_name", "card_name"],
          "player_id" => $player_id,
          "player_name" => $player_name,
          "this_name" => $this->getName(),
          "card_name" => $creeper_definition->getName(),
          "card" => $card,
          "creeper" => $card["type_arg"],
          "creeperCount" => Utils::getPlayerCreeperCount($player_id),
        ]
      );
    }

    // finally move Malfunction to discard pile
    $game->cards->playCard($card["id"]);

    $game->notifyAllPlayers(
      "keepersDiscarded",
      clienttranslate('${player_name} discarded <b>${card_name}</b>'),
      [
        "i18n" => ["card_name"],
        "player_name" => $player_name,
        "card_name" => $creeper_definition->getName(),
        "cards" => [$card],
        "player_id" => $player_id,
        "discardCount" => $game->cards->countCardInLocation("discard"),
        "creeperCount" => Utils::getPlayerCreeperCount($player_id),
      ]
    );
  }
}
