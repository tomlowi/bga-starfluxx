<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperTheScientist extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Scientist");
    $this->set = "vertical";

    $this->description = clienttranslate("Once during your turn, you can steal one of: Energy Being, Energy Crystals, Unseen Force, or Monolith.");
  }

  public function getKeeperType()
  {
    return "brains";
  }

  // @TODO: special ability Free Action once per turn
}
