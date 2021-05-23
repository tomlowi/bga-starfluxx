<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperExpendableCrewman extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Expendable Crewman");
    $this->set = "vertical";

    $this->description = clienttranslate("Any time another player takes or discards one of your Keepers... they must take this card instead.");
  }

  public function getKeeperType()
  {
    return "brains";
  }

}
