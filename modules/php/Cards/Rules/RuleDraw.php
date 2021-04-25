<?php
namespace StarFluxx\Cards\Rules;

use StarFluxx\Game\Utils;
/*
 * RuleDraw: base class for all Rule cards that adapt the current Draw rule
 */
class RuleDraw extends RuleCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);
  }

  public function getRuleType()
  {
    return "drawRule";
  }

  protected $drawCount;

  public function immediateEffectOnPlay($player)
  {
    $game = Utils::getGame();
    // current Draw Rule is changed immediately
    // active player might draw extra if they drew less at their turn start
    $oldValue = $game->getGameStateValue("drawnCards");
    // discard any other draw rules
    $game->discardRuleCardsForType("drawRule");
    // set new draw rule
    $game->setGameStateValue("drawRule", $this->drawCount);
    // draw extra cards for the difference
    if ($this->drawCount - $oldValue > 0) {
      $game->performDrawCards($player, $this->drawCount - $oldValue);
      $game->setGameStateValue("drawnCards", $this->drawCount);
    }
  }

  public function immediateEffectOnDiscard($player)
  {
    // reset to Basic Draw Rule
    Utils::getGame()->setGameStateValue("drawRule", 1);
  }
}
