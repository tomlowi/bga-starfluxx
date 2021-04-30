<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperTheDoctor extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Doctor");

    $this->description = clienttranslate("During your turn, if you have Brain Parasites, you can detach and discard them as long as they aren't attached to this card.");
  }

  public function getKeeperType()
  {
    return "brains";
  }
}
