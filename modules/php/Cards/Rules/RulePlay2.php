<?php
namespace StarFluxx\Cards\Rules;

use StarFluxx\Game\Utils;

class RulePlay2 extends RulePlay
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Play 2");
    $this->subtitle = clienttranslate("Replaces Play Rule");
    $this->description = clienttranslate(
      "Play 2 cards per turn. If you have fewer than that, play all your cards."
    );
  }

  protected $playCount = 2;
}
