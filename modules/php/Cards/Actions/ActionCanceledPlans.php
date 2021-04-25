<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;

class ActionCanceledPlans extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Take Another Turn");
    $this->description = clienttranslate(
      "Take another turn as soon as you finish this one. The maximum number of turns you can take in a row using this card is two."
    );
  }

  public function immediateEffectOnPlay($player)
  {
    Utils::getGame()->incGameStateValue("anotherTurnMark", 1);
  }
}
