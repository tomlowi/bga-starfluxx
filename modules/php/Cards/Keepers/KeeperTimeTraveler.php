<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperTimeTraveler extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Time Traveler");

    $this->description = clienttranslate("Time Portal goes back into your hand instead of the discard pile when you play it.");
  }

  public function getKeeperType()
  {
    return "brains";
  }

}
