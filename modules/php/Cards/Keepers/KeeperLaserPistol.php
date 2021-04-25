<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperLaserPistol extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Laser Pistol");

    $this->description = clienttranslate("Once during your turn, you can discard any Keeper anywhere in play (other than this one) if it has a Creeper attached to it.");
  }

  public function getKeeperType()
  {
    return "equipment";
  }
}
