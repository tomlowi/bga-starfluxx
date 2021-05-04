<?php
namespace StarFluxx\Cards\Creepers;

use StarFluxx\Game\Utils;
use starfluxx;

class CreeperBrainParasites extends CreeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Brain&nbsp;Parasites");
    $this->subtitle = clienttranslate("Place Immediately + Redraw");
    $this->description = clienttranslate(
      "If you have Keepers with brains in play, you must choose one to attach this to. Both cards stay together until discarded. You cannot win if you have this, unless the Goal says otherwise."
    );

    $this->help = clienttranslate(
      ""
    );
  }

  public function preventsWinForGoal($goalCard)
  {
    $requiredForGoals = [110];
    // Brain Parasites is required to win with these specific goals:
    // Evil Brain Parasites (110)
    if (in_array($goalCard->getUniqueId(), $requiredForGoals)) {
      return false;
    }

    return parent::preventsWinForGoal($goalCard);
  }

  public $interactionNeeded = "keeperSelectionSelf";

  public function onCheckResolveKeepersAndCreepers($lastPlayedCard)
  {
    // @TODO: when placed or any other time as soon as owner also has at least 1 Keeper with brains,
    // let player select the Keeper to attach this to => visualize and always discard together
    return null;
  } 

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    return null;
  }
}
