<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperBugEyedMonster extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Bug-Eyed Monster");
  }

  public function getKeeperType()
  {
    return "brains";
  }
}
