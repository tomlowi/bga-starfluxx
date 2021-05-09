<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;
use starfluxx;

class KeeperTheScientist extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Scientist");
    $this->set = "vertical";

    $this->description = clienttranslate("Once during your turn, you can steal one of: Energy Being, Energy Crystals, Unseen Force, or Monolith.");

    $this->examine_stuff = [5, 6, 23, 11];

    $this->help = clienttranslate(
      "Select any of the keepers the Scientist can examine in play from another player."
    );
  }

  public function getKeeperType()
  {
    return "brains";
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    $alreadyUsed = !Utils::playerHasNotYetUsedScientist();
    if ($alreadyUsed) return false;

    $scientist = $this->getUniqueId();
    $cards = Utils::getGame()->cards;
    
    $i = 0;
    while ($i < count($this->examine_stuff)) {
      $examine_keeper_card = array_values(
        $cards->getCardsOfType("keeper", $this->examine_stuff[$i])
      )[0];
      // at least 1 thing to examine available with other players?
      if ($examine_keeper_card["location"] == "keepers"
          && $examine_keeper_card["location_arg"] != $player_id) {
        return true;
      }
      $i++;
    }    

    return false;
  }

  public $interactionNeeded = "keeperSelectionOther";

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $game->setGameStateValue("playerTurnUsedScientist", 1);

    $card = $args["card"];    
    $card_type = $card["type"];
    $card_unique = $card["type_arg"];
    $card_location = $card["location"];
    $other_player_id = $card["location_arg"];

    if (
      $card_type != "keeper" ||
      $card_location != "keepers" ||
      $other_player_id == $player_id ||
      !in_array($card_unique, $this->examine_stuff)
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select one of the keepers the Scientist can examine in front of another player"
        )
      );
    }

    // move this keeper to the active player
    $notificationMsg = clienttranslate(
      '${player_name} stole <b>${card_name}</b> from ${player_name2}'
    );
    Utils::moveKeeperToPlayer($player_id, $card,
      $other_player_id, $player_id, $notificationMsg);
  }
}
