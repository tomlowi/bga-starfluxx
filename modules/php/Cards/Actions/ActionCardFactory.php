<?php

namespace StarFluxx\Cards\Actions;
use StarFluxx\Cards\CardFactory;
use StarFluxx\Game\Utils;
/*
 * ActionCardFactory: how to create Action Cards
 */
class ActionCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    $name = "StarFluxx\Cards\Actions\\" . self::$classes[$uniqueId];
    return $name;
  }

  public static function listCardDefinitions()
  {
    $actionDefinitions = [];

    $cardClasses = self::$classes;

    foreach ($cardClasses as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $actionDefinitions[$definitionId] = [
        "type" => "action",
        "subtype" => $card->getActionType(),
        "set" => $card->getCardSet(),
        "name" => $card->getName(),
        "description" => $card->getDescription(),
      ];
    }
    return $actionDefinitions;
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    301 => "ActionSpaceJackpot",
    302 => "ActionTimePortal",
    303 => "ActionWhatDoYouWant",
    304 => "ActionDraw2AndUseEm",
    305 => "ActionDraw3Play2",
    306 => "ActionExchangeKeepers",
    307 => "ActionLetsSimplify",
    308 => "ActionStealAKeeper",
    309 => "ActionTradeHands",
    310 => "ActionTrashANewRule",
    311 => "ActionBeamUsUp",
    312 => "ActionBrainTransference",
    313 => "ActionSonicSledgehammer",
    314 => "ActionRulesReset",
    315 => "ActionDistressCall",
    316 => "ActionCreeperReassignment",
    317 => "ActionItsATrap",
    318 => "ActionThatsMine",
    319 => "ActionVeto",
    320 => "ActionBelayThat",
    321 => "ActionCanceledPlans",    
  ];

}
