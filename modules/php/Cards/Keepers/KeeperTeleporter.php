<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperTeleporter extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Teleporter");

    $this->description = clienttranslate("Once during your turn, you can move any 1 of your Keepers to another player.");
  }

  public function getKeeperType()
  {
    return "equipment";
  }

  // @TODO: special ability Free Action once per turn
}
