<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperIntergalacticTravelGuide extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Intergalactic Travel Guide");
  }

  public function getKeeperType()
  {
    return "equipment";
  }
}
