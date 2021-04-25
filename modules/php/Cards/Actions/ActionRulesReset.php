<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleCardFactory;

class ActionRulesReset extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Rules Reset");
    $this->description = clienttranslate(
      "Reset to the Basic Rules. Discard all New Rule cards, and leave only the Basic Rules in play. Do not discard the current Goal."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $game->discardRuleCardsForType("playRule");
    $game->discardRuleCardsForType("drawRule");
    $game->discardRuleCardsForType("keepersLimit");
    $game->discardRuleCardsForType("handLimit");
    $game->discardRuleCardsForType("others");

    return "rulesChanged";
  }
}
