<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperLaserSword extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Laser Sword");

    $this->description = clienttranslate("Once during your turn, you can discard any Keeper you have in play (other than this one) if it has a Creeper attached to it.");
  }

  public function getKeeperType()
  {
    return "equipment";
  }

  // @TODO: special ability Free Action once per turn
}
