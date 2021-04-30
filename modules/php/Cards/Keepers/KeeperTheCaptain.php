<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperTheCaptain extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Captain");

    $this->description = clienttranslate("Once during your turn, you can steal one of: Scientist, Engineer, Doctor, or Expendable Crewman.");
  }

  public function getKeeperType()
  {
    return "brains";
  }
}
