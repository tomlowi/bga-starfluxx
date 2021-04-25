<?php
namespace StarFluxx\Cards\Rules;

use StarFluxx\Game\Utils;
/*
 * RuleKeeperLimit: base class for all Rule cards that adapt the current Keeper Limit
 */
class RuleKeeperLimit extends RuleCard
{
  public function getRuleType()
  {
    return "keepersLimit";
  }

  protected $keeperLimit;

  public function immediateEffectOnPlay($player)
  {
    // Discard old keepers limit card in play
    Utils::getGame()->discardRuleCardsForType("keepersLimit");
    // set new play rule
    Utils::getGame()->setGameStateValue("keepersLimit", $this->keeperLimit);

    return "keepersLimitRulePlayed";
  }

  public function immediateEffectOnDiscard($player)
  {
    // reset to Basic Keeper Limit = none
    Utils::getGame()->setGameStateValue("keepersLimit", -1);
  }
}
