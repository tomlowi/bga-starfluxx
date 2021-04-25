<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleCardFactory;

class ActionVeto extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("No Limits");
    $this->description = clienttranslate(
      "Discard all Hand and Keeper Limits currently in play."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $game->discardRuleCardsForType("handLimit");
    $game->discardRuleCardsForType("keepersLimit");
  }
}
