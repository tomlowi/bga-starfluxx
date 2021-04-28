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
    Utils::getGame()->performDrawCards($player_id, $extraCards);

    // @TODO: basic jackpot to be extended
  }
}
