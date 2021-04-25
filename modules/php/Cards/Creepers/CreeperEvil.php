<?php
namespace StarFluxx\Cards\Creepers;

use StarFluxx\Game\Utils;

class CreeperEvil extends CreeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Evil");
    $this->subtitle = clienttranslate("Place Immediately + Redraw");
    $this->description = clienttranslate(
      "You cannot win if you have this card."
    );
  }

  public function preventsWinForGoal($goalCard)
  {
    // when Baked Potato in play, this never prevents win here
    if (Utils::getActiveBakedPotato()) {
      return false;
    }

    return parent::preventsWinForGoal($goalCard);
  }

  public function onGoalChange()
  {
    $game = Utils::getGame();
    // check who has Radioactive potato in play now
    $card = array_values(
      $game->cards->getCardsOfType("creeper", $this->uniqueId)
    )[0];
    // if nobody, nothing to do
    if ($card["location"] != "keepers") {
      return null;
    }

    // otherwise, move potato to previous player
    $origin_player_id = $card["location_arg"];

    $directionTable = $game->getPrevPlayerTable();
    $destination_player_id = $directionTable[$origin_player_id];

    $game->cards->moveCard($card["id"], "keepers", $destination_player_id);

    $players = $game->loadPlayersBasicInfos();
    $destination_player_name = $players[$destination_player_id]["player_name"];

    $game->notifyAllPlayers(
      "keepersMoved",
      clienttranslate(
        'Goal change: <b>${card_name}</b> moves to ${player_name2}'
      ),
      [
        "i18n" => ["card_name"],
        "player_name2" => $destination_player_name,
        "card_name" => $this->name,
        "destination_player_id" => $destination_player_id,
        "origin_player_id" => $origin_player_id,
        "cards" => [$card],
        "destination_creeperCount" => Utils::getPlayerCreeperCount(
          $destination_player_id
        ),
        "origin_creeperCount" => Utils::getPlayerCreeperCount(
          $origin_player_id
        ),
      ]
    );

    return parent::onGoalChange();
  }
}
