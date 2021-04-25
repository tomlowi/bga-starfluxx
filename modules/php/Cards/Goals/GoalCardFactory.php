<?php

namespace StarFluxx\Cards\Goals;
use StarFluxx\Cards\CardFactory;
use StarFluxx\Game\Utils;
/*
 * GoalCardFactory: how to create Goal Cards
 */
class GoalCardFactory extends CardFactory
{
  public static function getCardFullClassName($uniqueId)
  {
    $name = "StarFluxx\Cards\Goals\\" . self::$classes[$uniqueId];    
    return $name;
  }

  public static function listCardDefinitions()
  {
    $goalDefinitions = [];

    $cardClasses = self::$classes;

    foreach ($cardClasses as $definitionId => $class) {
      $card = self::getCard(0, $definitionId);

      $goalDefinitions[$definitionId] = [
        "type" => "goal",
        "set" => $card->getCardSet(),
        "name" => $card->getName(),
        "subtitle" => $card->getSubtitle(),
        "description" => $card->getDescription(),
      ];
    }

    return $goalDefinitions;
  }

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    101 => "GoalFortyTwo",
    102 => "GoalArtificialIntelligence",
    103 => "GoalAlienArtifacts",
    104 => "GoalAlienLifeForms",
    105 => "GoalCityOfMonsters",
    106 => "GoalCityOfRobots",
    107 => "GoalThePowerOfTheDarkSide",
    108 => "GoalImDepressed",
    109 => "GoalEvilComputer",
    110 => "GoalEvilBrainParasites",
    111 => "GoalItsFullOfStars",
    112 => "GoalHesDeadCaptain",
    113 => "GoalImperialStarDestroyer",
    114 => "GoalLandingParty",
    115 => "GoalLaserWeapons",
    116 => "GoalLasersOnStun",
    117 => "GoalWhatDoctorWhere",
    118 => "GoalWereLostInSpace",
    119 => "GoalLunarArchaeology",
    120 => "GoalWeNeedMorePower",
    121 => "GoalSeekingNewCivilizations",
    122 => "GoalTheseArentTheDroids",
    123 => "GoalPlanetarySystem",
    124 => "GoalNoTroubleAtAll",
    125 => "GoalTheRobotsHaveTurnedAgainstUs",
    126 => "GoalSpaceDock",
    127 => "GoalStarWarriors",
    128 => "GoalStarshipCaptain",
    129 => "GoalStarshipFuel",
    130 => "GoalStrangePowers",
    131 => "GoalThatsNoMoon",
    132 => "GoalMyTimeMachineWorks",
    133 => "GoalToTheStars",
  ];

}
