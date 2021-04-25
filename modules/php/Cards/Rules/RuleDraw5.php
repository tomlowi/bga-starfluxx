<?php
namespace StarFluxx\Cards\Rules;

use StarFluxx\Game\Utils;

class RuleDraw5 extends RuleDraw
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Draw 5");
    $this->subtitle = clienttranslate("Replaces Draw Rule");
    $this->description = clienttranslate(
      "Draw 5 cards per turn. If you just played this card, draw extra cards as needed to reach 5 cards drawn."
    );
  }

  protected $drawCount = 5;
}
