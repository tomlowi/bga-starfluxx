<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperCuteFuzzyAlienCreature extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Cute Fuzzy Alien Creature");

    $this->description = clienttranslate("Move this card to the next player when your turn ends. If this card is discarded from play, move it to the top of the draw pile instead.");
  }

  public function getKeeperType()
  {
    return "brains";
  }
}
