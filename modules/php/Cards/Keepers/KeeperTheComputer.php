<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperTheComputer extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Computer");

    $this->description = clienttranslate("Draw and play one extra card during your turn. You may also exceed the Hand and Keeper Limits by one.");
  }

  public function getKeeperType()
  {
    return "equipment";
  }

  public function immediateEffectOnPlay($player_id)
  {
    // Draw rule is adapted immediately, so current player draws an extra card
    // But don't do it here, instead do it just before next card play - creeper attach might have to be resolved first!
    //Utils::checkForDrawComputerBonus($player_id);
  }

  public function immediateEffectOnDiscard($player_id)
  {
    // Computer got discarded => we will need to recheck Hand & Keeper Limit
    Utils::getGame()->setGameStateValue("forcedLimitsCheck", 1);
  }

  public function onMalfunction()
  {
    // Computer became Malfunctioning => we will need to recheck Hand & Keeper Limit
    Utils::getGame()->setGameStateValue("forcedLimitsCheck", 1);
  }

  public function onMoveAwayFromPlayer($origin_player_id)
  {
    // Computer moved to other player or hand => we will need to recheck Hand & Keeper Limit
    Utils::getGame()->setGameStateValue("forcedLimitsCheck", 1);
  }

  public static function notifyActiveFor($player_id)
  {
    $game = Utils::getGame();
    // only log once per turn, otherwise clutters log after each card played
    $alreadyLogged = $game->getGameStateValue("playerTurnLoggedComputerBonus");
    if ($alreadyLogged != 0) {
      return;
    }

    $game->notifyAllPlayers(
      "computerBonus",
      clienttranslate('${player_name} gets <b>Computer Bonus</b>'),
      [
        "player_id" => $player_id,
        "player_name" => $game->getActivePlayerName(),
      ]
    );

    $game->setGameStateValue("playerTurnLoggedComputerBonus", 1);
  }
}
