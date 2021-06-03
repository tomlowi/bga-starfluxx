<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleCardFactory;
use starfluxx;

class ActionBeamUsUp extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Beam Us Up!");
    $this->description = clienttranslate(
      "All beings in play are returned to the hands of their owners, unless someone has the Teleport Chamber in play, in which case that player takes all beings in play and adds them to their hand."
    );

  }

  public $interactionNeeded = null;

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    // check if someone has the teleporter in play (and it is not Malfunctioning!)
    $to_player_id = null;
    $keeperTeleporter = 16;
    $teleporter_player = Utils::findPlayerWithKeeper($keeperTeleporter);
    if ($teleporter_player != null) {
      if (!Utils::checkForMalfunction($teleporter_player["keeper_card"]["id"])) {
        $to_player_id = $teleporter_player["player_id"];
      }
    }

    // check keepers in play for all players
    $players = $game->loadPlayersBasicInfos();
    $player_name = $players[$player_id]["player_name"];

    $game->notifyAllPlayers(
      "actionDone",
      clienttranslate('${player_name} uses <b>${card_name}</b> to teleport all beings'),
      [
        "i18n" => ["card_name"],
        "card_name" => $this->getName(),
        "player_name" => $player_name,
      ]
    );

    $players_id = array_keys($players);    
    foreach ($players_id as $check_player_id) {
      $player_cards = $game->cards->getCardsInLocation("keepers", $check_player_id);
      foreach ($player_cards as $card_id => $card) {
        // "beings" = keepers with brains, see https://faq.looneylabs.com/question/462
        if ($card["type"] == "keeper") {
          $card_definition = $game->getCardDefinitionFor($card);
          if ($card_definition->getKeeperType() == "brains") {
            // move beings to the teleporter or to their owner's hand
            $target_player_id = $to_player_id ?? $check_player_id;            
            $origin_player_id = $card["location_arg"];
            Utils::moveKeeperToHand($player_id, $card,
              $origin_player_id, $target_player_id, "");            
          }
        }
      }
    }

    return parent::immediateEffectOnPlay($player_id);
  }

}
