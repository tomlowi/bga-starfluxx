<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;

class ActionDistressCall extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Distress Call");
    $this->description = clienttranslate(
      "All players draw 1 card from the deck. Anyone with a Creeper then draws additional cards until they have drawn a total of 2 cards for each Creeper they possess."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    // If extra creepers are drawn, they must immediately be played
    // so we need activated players for that!

    $active_player = $game->getActivePlayerId();
    $players = $game->loadPlayersBasicInfos();

    // make sure we can't draw back this card itself (after reshuffle if deck would be empty)
    $game->cards->moveCard($this->getCardId(), "side", $player_id);

    // draw 1 card for each player first
    foreach ($players as $player_id => $player) {
      $game->performDrawCards($player_id, 1, true);                  
    }
    // then check for creepers and draw extra 2 per Creeper
    foreach ($players as $player_id => $player) {

      $creeperCount = Utils::getPlayerCreeperCount($player_id);
      if ($creeperCount == 0)
        continue;

      $game->performDrawCards($player_id, $creeperCount*2, true);
      // this also means re-check and draw extra for creepers just drawn
      $extraCreeperCount = 0;
      do {
        $creeperCountNew = Utils::getPlayerCreeperCount($player_id);
        $extraCreeperCount = $creeperCountNew - $creeperCount;
        if ($extraCreeperCount > 0) {
          $creeperCount = $creeperCountNew;
          $game->performDrawCards($player_id, $extraCreeperCount*2, true);
        }        
      } while ($extraCreeperCount > 0);
    }

    // move this card itself back to the discard pile
    $game->cards->playCard($this->getCardId());

    // we gave cards to other players: check for hand limits
    return "handLimitRulePlayed";
  }
}
