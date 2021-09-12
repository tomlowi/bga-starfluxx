<?php
namespace StarFluxx\Cards\Creepers;

use StarFluxx\Game\Utils;
use starfluxx;

class CreeperMalfunction extends CreeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Malfunction");
    $this->subtitle = clienttranslate("Place Immediately + Redraw");
    $this->description = clienttranslate(
      "If you have any equipment Keepers in play, you must choose one to attach this to. Any special powers of the Keeper become ignored. Both cards stay together until discarded. You cannot win if you have this, unless the Goal says otherwise."
    );

    $this->help = clienttranslate(
      "Select any of the equipment Keepers in play from the player that gets Malfunction."
    );
  }

  public function preventsWinForGoal($goalCard)
  {
    $requiredForGoals = [118];
    // Malfunction is required to win with these specific goals:
    // GoalWereLostInSpace (118)
    if (in_array($goalCard->getUniqueId(), $requiredForGoals)) {
      return false;
    }

    return parent::preventsWinForGoal($goalCard);
  }

  // When Malfunction attached: any special powers of the Keeper become ignored!
  public function isAttachedTo()
  {
    $game = Utils::getGame();
    return $game->getGameStateValue("creeperMalfunctionAttachedTo");
  }

  public function detach()
  {
    $game = Utils::getGame();
    $game->setGameStateValue("creeperMalfunctionAttachedTo", -1);
  }

  public $interactionNeeded = "keeperSelectionForCreeper";

  public function onCheckResolveKeepersAndCreepers($lastPlayedCard)
  {
    $game = Utils::getGame();

    $attachedTo = $game->getGameStateValue("creeperMalfunctionAttachedTo");
    if ($attachedTo > -1) {
      return; // already attached to a Keeper, nothing to do
    }

    // when placed or any other time as soon as owner also has at least 1 Keeper equipment,
    // let player select the Keeper to attach this to => visualize and always discard together
    $creeper_player = $this->findPlayerWithThisCreeper();
    if ($creeper_player == null) {
      return null;
    }
    $creeper_player_id = $creeper_player["player_id"];
    $keepersEquipment = $this->findKeepersOfType($creeper_player_id, "equipment");
    if (empty($keepersEquipment)) {
      return null;
    }

    $malfunction_card = $creeper_player["creeper_card"];
    $game->setGameStateValue("creeperToResolvePlayerId", $creeper_player_id);
    $game->setGameStateValue("creeperToResolveCardId", $malfunction_card["id"]);
    return parent::onCheckResolveKeepersAndCreepers($malfunction_card);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    
    $card = $args["card"];
    $card_definition = $game->getCardDefinitionFor($card);

    $card_type = $card["type"];
    $card_location = $card["location"];
    $origin_player_id = $card["location_arg"];

    $creeper_player_id = $player_id;
    $creeper_player = $this->findPlayerWithThisCreeper();
    if ($creeper_player != null) {
      $creeper_player_id = $creeper_player["player_id"];
    }
    
    // Malfunction can only be attached to a Keeper equipment in play for this player
    if (
      ($card_type != "keeper") ||
      $card_location != "keepers" ||
      $origin_player_id != $creeper_player_id ||
      $card_definition->getKeeperType() != "equipment"
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper equipment in play from the player with this creeper"
        )
      );
    }

    $game->setGameStateValue("creeperMalfunctionAttachedTo", $card["id"]);

    $players = $game->loadPlayersBasicInfos();
    $player_name = $players[$player_id]["player_name"];

    $game->notifyAllPlayers(
      "creeperAttached",
      clienttranslate('${player_name} attaches <b>${creeper_name}</b> to <b>${keeper_name}</b>'),
      [
        "i18n" => ["creeper_name", "keeper_name"],
        "player_id" => $player_id,
        "player_name" => $player_name,
        "creeper_name" => $this->getName(),
        "keeper_name" => $card_definition->getName(),
        "card" => $card,
        "creeper" => $this->getUniqueId(),
        "creeperCount" => Utils::getPlayerCreeperCount($origin_player_id),
      ]
    );
  }
}
