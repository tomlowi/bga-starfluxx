<?php

namespace StarFluxx\Cards\Keepers;
use StarFluxx\Cards\CardFactory;
/*
 * KeeperCardFactory: how to create Keeper Cards
 */
class KeeperCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    $name = "StarFluxx\Cards\Keepers\\" . self::$classes[$uniqueId];
    return $name;
  }

  public static function listCardDefinitions()
  {
    $keeperDefinitions = [];

    // markers to enable client translations
    $keeperSubTypeBrains = clienttranslate("brains");
    $keeperSubTypeEquipment = clienttranslate("equipment");

    foreach (self::$classes as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $keeperDefinitions[$definitionId] = [
        "type" => "keeper",
        "subtype" => $card->getKeeperType(),
        "set" => $card->getCardSet(),
        "name" => $card->getName(),
        "subtitle" => $card->getSubtitle(),
        "description" => $card->getDescription(),
      ];
    }  

    return $keeperDefinitions;
  }

  /* trigger all Keepers in play that have a special ability on end of turn */
  public static function onTurnEnd()
  {
    foreach (self::$classes as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $stateTransition = $card->onTurnEnd();
      if ($stateTransition != null) {
        return $stateTransition;
      }
    }
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   * no need for separate Keeper Card files, Keepers have no game logic
   */
  public static $classes = [
    1 => "KeeperAlienCity",
    2 => "KeeperBugEyedMonster",
    3 => "KeeperTheComputer",
    4 => "KeeperDistantPlanet",
    5 => "KeeperEnergyBeing",
    6 => "KeeperEnergyCrystals",
    7 => "KeeperCuteFuzzyAlienCreature",
    8 => "KeeperHolographicProjection",
    9 => "KeeperLaserPistol",
    10 => "KeeperLaserSword",
    11 => "KeeperMonolith",
    12 => "KeeperExpendableCrewman",
    13 => "KeeperSpaceStation",
    14 => "KeeperTheStars",
    15 => "KeeperStarship",
    16 => "KeeperTeleporter",
    17 => "KeeperTheCaptain",
    18 => "KeeperTheDoctor",
    19 => "KeeperTheEngineer",
    20 => "KeeperTheScientist",
    21 => "KeeperTimeTraveler",
    22 => "KeeperIntergalacticTravelGuide",
    23 => "KeeperUnseenForce",
    24 => "KeeperTheRobot",
    25 => "KeeperSmallMoon",
  ];
}
