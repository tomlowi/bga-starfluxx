<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionTradeHands extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Trade Hands");
    $this->description = clienttranslate(
      "Trade your hand for the hand of one of your opponents. This is one of those times when you can get something for nothing!"
    );

    $this->help = clienttranslate(
      "Choose the player you want to trade hands with."
    );
  }

  public $interactionNeeded = "playerSelection";

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $players = $game->loadPlayersBasicInfos();

    $player_name = $players[$player_id]["player_name"];
    $selected_player_id = $args["selected_player_id"];
    $selected_player_name = $players[$selected_player_id]["player_name"];

    $selected_player_hand = $game->cards->getCardsInLocation(
      "hand",
      $selected_player_id
    );
    $player_hand = $game->cards->getCardsInLocation("hand", $player_id);

    $game->cards->moveCards(
      array_keys($selected_player_hand),
      "hand",
      $player_id
    );
    $game->cards->moveCards(
      array_keys($player_hand),
      "hand",
      $selected_player_id
    );
    $game->notifyPlayer($player_id, "cardsSentToPlayer", "", [
      "cards" => $player_hand,
      "player_id" => $selected_player_id,
    ]);
    $game->notifyPlayer($player_id, "cardsReceivedFromPlayer", "", [
      "cards" => $selected_player_hand,
      "player_id" => $selected_player_id,
    ]);
    $game->notifyPlayer($selected_player_id, "cardsSentToPlayer", "", [
      "cards" => $selected_player_hand,
      "player_id" => $player_id,
    ]);
    $game->notifyPlayer($selected_player_id, "cardsReceivedFromPlayer", "", [
      "cards" => $player_hand,
      "player_id" => $player_id,
    ]);

    $game->notifyAllPlayers(
      "actionDone",
      clienttranslate('${player_name} trades hands with ${player_name2}'),
      [
        "player_name" => $player_name,
        "player_name2" => $selected_player_name,
      ]
    );
    $game->sendHandCountNotifications();

    return "handsExchangeOccured";
  }
}
