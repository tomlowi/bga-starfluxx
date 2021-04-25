<?php

namespace StarFluxx\Cards\Rules;
use StarFluxx\Cards\CardFactory;
use StarFluxx\Game\Utils;
/*
 * RuleCardFactory: how to create Rule Cards
 */
class RuleCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    $name = "StarFluxx\Cards\Rules\\" . self::$classes[$uniqueId];    
    return $name;
  }

  public static function listCardDefinitions()
  {
    $ruleDefinitions = [];

    $cardClasses = self::$classes;

    foreach ($cardClasses as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $ruleDefinitions[$definitionId] = [
        "type" => "rule",
        "ruleType" => $card->getRuleType(),
        "set" => $card->getCardSet(),
        "name" => $card->getName(),
        "subtitle" => $card->getSubtitle(),
        "description" => $card->getDescription(),
      ];
    }

    return $ruleDefinitions;
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    201 => "RuleDoubleAgenda",
    202 => "RuleGetOnWithIt",
    203 => "RuleWormhole",
    204 => "RuleDraw2",
    205 => "RuleDraw3",
    206 => "RuleDraw4",
    207 => "RuleDraw5",
    208 => "RuleHandLimit1",
    209 => "RuleHandLimit2",
    210 => "RuleHandLimit3",
    211 => "RuleHandLimit4",
    212 => "RuleKeeperLimit3",
    213 => "RuleKeeperLimit4",
    214 => "RulePlay2",
    215 => "RulePlay3",
    216 => "RulePlay4",
    217 => "RulePlayAll",
  ];

}
