<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperHolographicProjection extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Holographic Projection");
    $this->set = "vertical";

    $this->description = clienttranslate("You can win with a Keeper another player has, as it were in front of you, not them, but only during your turn.");
  }

  public function getKeeperType()
  {
    return "equipment";
  }

  // special ability for goal victory check => see goalReachedByPlayer in Goal cards
}
