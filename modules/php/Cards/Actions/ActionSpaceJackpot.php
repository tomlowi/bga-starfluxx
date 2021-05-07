<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;

class ActionSpaceJackpot extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Space Jackpot!");
    $this->description = clienttranslate("Draw 5 extra cards, add them to your hand, then discard 2 cards.");
  }

  public function immediateEffectOnPlay($player_id)
  {
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $extraCards = 3 + $addInflation;
    Utils::getGame()->performDrawCards($player_id, $extraCards, true);

    // @TODO: basic jackpot (draw 3) to be extended to (draw 5, then discard 2)
    // might reuse some part of the EnforceHandLimitSelf, but need to check state transitions then
  }
}
