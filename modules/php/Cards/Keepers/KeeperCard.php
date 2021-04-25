<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Cards\Card;
use StarFluxx\Game\Utils;
/*
 * KeeperCard: simple class to handle all keeper cards
 */
class KeeperCard extends Card
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);    
  }

  public function getKeeperType()
  {
    return "basic";
  }
}
