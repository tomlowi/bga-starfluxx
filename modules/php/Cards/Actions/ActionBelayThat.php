<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;

class ActionBelayThat extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Share the Wealth");
    $this->description = clienttranslate(
      "Gather up all the Keepers on the table, shuffle them together, and deal them back out to all players, starting with yourself. These go immediately into play in front of their new owners. Everyone will probably end up with a different number of Keepers in play than they started with."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $keepersInPlay = $game->cards->getCardsOfTypeInLocation(
      "keeper",
      null,
      "keepers",
      null
    );
    $next_player = $game->getNextPlayerTable();

    // gather and shuffle all keepers in play
    shuffle($keepersInPlay);

    // deal them back out, starting with the current player
    $current_player_id = $player_id;

    foreach ($keepersInPlay as $card) {
      if ($current_player_id != $card["location_arg"]) {
        $origin_player_id = $card["location_arg"];
        $game->cards->moveCard($card["id"], "keepers", $current_player_id);
        $game->notifyAllPlayers("keepersMoved", "", [
          "destination_player_id" => $current_player_id,
          "origin_player_id" => $origin_player_id,
          "cards" => [$card],
          "destination_creeperCount" => Utils::getPlayerCreeperCount(
            $current_player_id
          ),
          "origin_creeperCount" => Utils::getPlayerCreeperCount(
            $origin_player_id
          ),
        ]);
      }
      $current_player_id = $next_player[$current_player_id];
    }
    return "keepersExchangeOccured";
  }
}
