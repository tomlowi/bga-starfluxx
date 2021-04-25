<?php
namespace StarFluxx\Cards\Rules;

use StarFluxx\Game\Utils;
/*
 * RuleHandLimit: base class for all Rule cards that adapt the current Hand Limit
 */
class RuleHandLimit extends RuleCard
{
  public function getRuleType()
  {
    return "handLimit";
  }

  protected $handLimit;

  public function immediateEffectOnPlay($player)
  {
    // Discard old hand limit card in play
    Utils::getGame()->discardRuleCardsForType("handLimit");
    // set new hand limit rule
    Utils::getGame()->setGameStateValue("handLimit", $this->handLimit);

    return "handLimitRulePlayed";
  }

  public function immediateEffectOnDiscard($player)
  {
    // reset to Basic Hand Limit = none
    Utils::getGame()->setGameStateValue("handLimit", -1);
  }
}
