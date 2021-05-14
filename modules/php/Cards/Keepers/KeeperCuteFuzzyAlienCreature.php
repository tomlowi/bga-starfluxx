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

  // @TODO: when discarded, goes back to top of draw pile

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

    $card = $CFA_player["keeper_card"];
    // but if it is, it should be moved to the next player
    $directionTable = $game->getNextPlayerTable();
    $destination_player_id = $directionTable[$origin_player_id];

    $notificationMsg = clienttranslate(
      'Turn end: <b>${card_name}</b> moves to ${player_name2}'
    );
    Utils::moveKeeperToPlayer($origin_player_id, $card,
      $origin_player_id, $destination_player_id, $notificationMsg); 

  }
}
