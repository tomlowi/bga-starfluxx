<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperTheEngineer extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Engineer");

    $this->description = clienttranslate("During your turn, if you have Malfunction, you can detach and discard it.");
  }

  public function getKeeperType()
  {
    return "brains";
  }
}
