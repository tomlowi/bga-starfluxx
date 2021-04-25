<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;

class ActionSpaceJackpot extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Jackpot!");
    $this->description = clienttranslate("Draw 3 extra cards!");
  }

  public function immediateEffectOnPlay($player_id)
  {
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $extraCards = 3 + $addInflation;
    Utils::getGame()->performDrawCards($player_id, $extraCards);
  }
}
