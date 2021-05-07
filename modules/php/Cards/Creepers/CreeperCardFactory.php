<?php

namespace StarFluxx\Cards\Creepers;
use StarFluxx\Cards\CardFactory;
use StarFluxx\Game\Utils;
/*
 * CreeperCardFactory: how to create Creeper Cards
 */
class CreeperCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    $name = "StarFluxx\Cards\Creepers\\" . self::$classes[$uniqueId];
    return $name;
  }

  public static function listCardDefinitions()
  {
    $creeperDefinitions = [];

    foreach (self::$classes as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $creeperDefinitions[$definitionId] = [
        "type" => "creeper",
        "set" => $card->getCardSet(),
        "name" => $card->getName(),
        "subtitle" => $card->getSubtitle(),
        "description" => $card->getDescription(),
      ];
    }  

    return $creeperDefinitions;
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   * no need for separate Creeper Card files, Creepers have no game logic
   */
  public static $classes = [
    51 => "CreeperBrainParasites",
    52 => "CreeperEvil",
    53 => "CreeperMalfunction",
  ];

  /* trigger all Creepers in play that have a special ability when Goal changes */
  public static function onGoalChange()
  {
    foreach (self::$classes as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $card->onGoalChange();
    }
  }

  /* trigger all Creepers in play that have a special ability on start of turn */
  public static function onTurnStart()
  {
    foreach (self::$classes as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $stateTransition = $card->onTurnStart();
      if ($stateTransition != null) {
        return $stateTransition;
      }
    }
  }

  /* trigger all Creepers in play that have a special ability to be checked after every change */
  public static function onCheckResolveKeepersAndCreepers($lastPlayedCard)
  {
    foreach (self::$classes as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $stateTransition = $card->onCheckResolveKeepersAndCreepers(
        $lastPlayedCard
      );
      if ($stateTransition != null) {
        return $stateTransition;
      }
    }
  }
}
