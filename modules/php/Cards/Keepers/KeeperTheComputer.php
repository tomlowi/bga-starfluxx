<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperTheComputer extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Computer");

    $this->description = clienttranslate("Draw and play one extra card during your turn. You may also exceed the Hand and Keeper Limits by one.");
  }

  public function getKeeperType()
  {
    return "equipment";
  }
}
