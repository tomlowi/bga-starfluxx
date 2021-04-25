<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleCardFactory;

class ActionCreeperSweeper extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->set = "creeperpack";
    $this->name = clienttranslate("Creeper Sweeper");
    $this->description = clienttranslate("All Creepers in play are discarded.");
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $creeperCards = $game->cards->getCardsOfTypeInLocation(
      "creeper",
      null,
      "keepers",
      null
    );

    $creeperCardsPerPlayer = [];
    foreach ($creeperCards as $card_id => $card) {
      $player_id = $card["location_arg"];
      if (!array_key_exists($player_id, $creeperCardsPerPlayer)) {
        $creeperCardsPerPlayer[$player_id] = [];
      }
      $creeperCardsPerPlayer[$player_id][] = $card;
      $game->cards->playCard($card_id);
    }

    foreach ($creeperCardsPerPlayer as $player_id => $discards_for_player) {
      $game->notifyAllPlayers("keepersDiscarded", "", [
        "player_id" => $player_id,
        "cards" => $discards_for_player,
        "discardCount" => $game->cards->countCardInLocation("discard"),
        "creeperCount" => 0,
      ]);
    }
  }
}
