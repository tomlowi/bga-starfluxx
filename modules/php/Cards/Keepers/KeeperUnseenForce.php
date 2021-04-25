<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperUnseenForce extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Unseen Force");

    $this->description = clienttranslate("Once during your turn, you can steal a card chosen randomly from another player's hand and add that card to your own hand.");
  }
}
