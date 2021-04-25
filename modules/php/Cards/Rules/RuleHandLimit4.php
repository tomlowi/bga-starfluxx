<?php
namespace StarFluxx\Cards\Rules;

use StarFluxx\Game\Utils;

class RuleHandLimit4 extends RuleHandLimit
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Hand Limit 4");
    $this->subtitle = clienttranslate("Replaces Hand Limit");
    $this->description = clienttranslate(
      "If it isn't your turn, you can only have 4 cards in your hand. Discard extras immediately. During your turn, this rule does not apply to you, after your turn ends, discard down to 4 cards."
    );
  }

  protected $handLimit = 4;
}
