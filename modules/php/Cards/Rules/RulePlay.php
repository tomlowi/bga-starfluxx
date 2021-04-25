<?php
namespace StarFluxx\Cards\Rules;

use StarFluxx\Game\Utils;
/*
 * RulePlay: base class for all Rule cards that adapt the current Play rule
 */
class RulePlay extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  public function getRuleType()
  {
    return "playRule";
  }

  protected $playCount;

  public function immediateEffectOnPlay($player)
  {
    // Discard old play limit card in play
    Utils::getGame()->discardRuleCardsForType("playRule");
    // set new play rule
    Utils::getGame()->setGameStateValue("playRule", $this->playCount);
  }

  public function immediateEffectOnDiscard($player)
  {
    // reset to Basic Play Rule
    Utils::getGame()->setGameStateValue("playRule", 1);
  }
}
