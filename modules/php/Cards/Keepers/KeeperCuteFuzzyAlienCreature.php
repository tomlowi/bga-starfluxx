<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperCuteFuzzyAlienCreature extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Cute Fuzzy Alien Creature");

    $this->description = clienttranslate("Move this card to the next player when your turn ends. If this card is discarded from play, move it to the top of the draw pile instead.");
  }

  public function getKeeperType()
  {
    return "brains";
  }

  public function onTurnEnd()
  {
    $game = Utils::getGame();

    $CFA_player = $this->findPlayerWithThisKeeper();
    if ($CFA_player == null) return;

    $origin_player_id = $game->getActivePlayerId();
    // if CuteFuzzyAlien is not with the active player, nothing to do
    if ($origin_player_id != $CFA_player["player_id"]) {
      return;
    }

    // but if it is, it should be moved to the next player
    $directionTable = $game->getNextPlayerTable();
    $destination_player_id = $directionTable[$origin_player_id];

    $game->notifyAllPlayers(
      "keepersMoved",
      clienttranslate(
        'Turn end: <b>${card_name}</b> moves to ${player_name2}'
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
  }
}
