<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;
use starfluxx;

class KeeperTheCaptain extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("The&nbsp;Captain");
    $this->set = "vertical";

    $this->description = clienttranslate("Once during your turn, you can steal one of: Scientist, Engineer, Doctor, or Expendable Crewman.");

    $this->callable_crew = [12, 18, 19, 20];

    $this->help = clienttranslate(
      "Select any of the Captain's crew members in play from another player."
    );
  }

  public function getKeeperType()
  {
    return "brains";
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    $alreadyUsed = !Utils::playerHasNotYetUsedCaptain();
    if ($alreadyUsed) return false;

    $captain = $this->getUniqueId();
    $cards = Utils::getGame()->cards;
    
    $i = 0;
    while ($i < count($this->callable_crew)) {
      $crew_keeper_card = array_values(
        $cards->getCardsOfType("keeper", $this->callable_crew[$i])
      )[0];
      // at least 1 callable crew member available with other players?
      if ($crew_keeper_card["location"] == "keepers"
          && $crew_keeper_card["location_arg"] != $player_id) {
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
    $game->setGameStateValue("playerTurnUsedCaptain", 1);

    $card = $args["card"];    
    $card_type = $card["type"];
    $card_unique = $card["type_arg"];
    $card_location = $card["location"];
    $other_player_id = $card["location_arg"];

    if (
      $card_type != "keeper" ||
      $card_location != "keepers" ||
      $other_player_id == $player_id ||
      !in_array($card_unique, $this->callable_crew)
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a crew member keeper card in front of another player"
        )
      );
    }

    // move this keeper to the active player
    $notificationMsg = clienttranslate(
      '${player_name} stole <b>${card_name}</b> from ${player_name2}'
    );
    $stolen_card_id = Utils::moveKeeperToPlayer($player_id, $card,
      $other_player_id, $player_id, $notificationMsg);

    // check if target player could counter this keeper steal
    return Utils::checkCounterTrapForKeeperStolen($other_player_id, $stolen_card_id);
  }
}
