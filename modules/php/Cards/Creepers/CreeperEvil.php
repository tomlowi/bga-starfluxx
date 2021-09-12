<?php
namespace StarFluxx\Cards\Creepers;

use StarFluxx\Game\Utils;
use starfluxx;

class CreeperEvil extends CreeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Evil");
    $this->subtitle = clienttranslate("Place Immediately + Redraw");
    $this->description = clienttranslate(
      "If you have Keepers in play, you must choose one to attach this to. Both cards stay together until discarded. You cannot win if you have this, unless the Goal says otherwise."
    );

    $this->help = clienttranslate(
      "Select any of the Keepers in play from the player that gets Evil."
    );
  }

  public function preventsWinForGoal($goalCard)
  {
    $requiredForGoals = [107, 109, 110, 113, 125];
    // Evil is required to win with these specific goals:
    // GoalThePowerOfTheDarkSide (107)
    // GoalEvilComputer (109)
    // GoalEvilBrainParasites (110)
    // GoalImperialStarDestroyer (113)
    // GoalTheRobotsHaveTurnedAgainstUs (125)
    if (in_array($goalCard->getUniqueId(), $requiredForGoals)) {
      return false;
    }

    return parent::preventsWinForGoal($goalCard);
  }

  public function isAttachedTo()
  {
    $game = Utils::getGame();
    return $game->getGameStateValue("creeperEvilAttachedTo");
  }

  public function detach()
  {
    $game = Utils::getGame();
    $game->setGameStateValue("creeperEvilAttachedTo", -1);
  }

  public $interactionNeeded = "keeperSelectionForCreeper";

  public function onCheckResolveKeepersAndCreepers($lastPlayedCard)
  {
    $game = Utils::getGame();

    $attachedTo = $game->getGameStateValue("creeperEvilAttachedTo");
    if ($attachedTo > -1) {
      return; // already attached to a Keeper, nothing to do
    }

    // when placed or any other time as soon as owner also has at least 1 Keeper (any),
    // let player select the Keeper to attach this to => visualize and always discard together
    $creeper_player = $this->findPlayerWithThisCreeper();
    if ($creeper_player == null) {
      return null;
    }
    $creeper_player_id = $creeper_player["player_id"];
    $keepersAny = $this->findKeepersOfType($creeper_player_id, null);
    if (empty($keepersAny)) {
      return null;
    }

    $evil_card = $creeper_player["creeper_card"];
    $game->setGameStateValue("creeperToResolvePlayerId", $creeper_player_id);
    $game->setGameStateValue("creeperToResolveCardId", $evil_card["id"]);
    return parent::onCheckResolveKeepersAndCreepers($evil_card);
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
    
    // Evil can only be attached to a Keeper (any) in play for this player
    if (
      ($card_type != "keeper") ||
      $card_location != "keepers" ||
      $origin_player_id != $creeper_player_id
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper in play from the player with this creeper"
        )
      );
    }

    $game->setGameStateValue("creeperEvilAttachedTo", $card["id"]);

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
