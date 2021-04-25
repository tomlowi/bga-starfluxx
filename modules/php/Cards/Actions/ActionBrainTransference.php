<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionBrainTransference extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Rotate Hands");
    $this->description = clienttranslate(
      "All players pass their hands to the player next to them. You decide which direction."
    );

    $this->help = clienttranslate(
      "Choose the direction. To the right means you will get your cards from the previous player, to the left means you will get them from the next player."
    );
  }

  public $interactionNeeded = "buttons";

  public function resolveArgs()
  {
    return [
      ["value" => "left", "label" => clienttranslate("To the left")],
      ["value" => "right", "label" => clienttranslate("To the right")],
    ];
  }

  public function resolvedBy($active_player_id, $args)
  {
    $direction = $args["value"];

    $game = Utils::getGame();

    $players = $game->loadPlayersBasicInfos();
    $player_name = $players[$active_player_id]["player_name"];

    $startingHands = [];

    foreach ($players as $player_id => $player) {
      $startingHands[$player_id] = $game->cards->getCardsInLocation(
        "hand",
        $player_id
      );
    }

    if ($direction == "left") {
      $directionTable = $game->getNextPlayerTable();
      $msg = clienttranslate('${player_name} rotated hands to the left');
    } else {
      $directionTable = $game->getPrevPlayerTable();
      $msg = clienttranslate('${player_name} rotated hands to the right');
    }

    foreach ($players as $player_id => $player) {
      $selected_player_id = $directionTable[$player_id];

      $newHand = $startingHands[$selected_player_id];

      $game->notifyPlayer($selected_player_id, "cardsSentToPlayer", "", [
        "cards" => $newHand,
        "player_id" => $player_id,
      ]);
      $game->notifyPlayer($player_id, "cardsReceivedFromPlayer", "", [
        "cards" => $newHand,
        "player_id" => $selected_player_id,
      ]);
      $game->cards->moveCards(array_keys($newHand), "hand", $player_id);
    }

    $game->notifyAllPlayers("actionDone", $msg, [
      "player_name" => $player_name,
    ]);

    $game->sendHandCountNotifications();

    return "handsExchangeOccured";
  }
}
