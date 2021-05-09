<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionExchangeKeepers extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Exchange Keepers");
    $this->description = clienttranslate(
      "Pick any Keeper another player has on the table and exchange it for one you have on the table. If you have no Keepers in play, or if no one else has a Keeper, nothing happens."
    );

    $this->help = clienttranslate(
      "Select exactly 2 Keeper cards, 1 of yours and 1 of another player."
    );
  }

  public $interactionNeeded = "keepersExchange";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $totalKeepersInPlay = count(
      $game->cards->getCardsOfTypeInLocation("keeper", null, "keepers", null)
    );
    $playersKeepersInPlay = count(
      $game->cards->getCardsOfTypeInLocation(
        "keeper",
        null,
        "keepers",
        $player_id
      )
    );
    if (
      $playersKeepersInPlay == 0 ||
      $totalKeepersInPlay - $playersKeepersInPlay == 0
    ) {
      // no keepers on my side or
      // no keepers on the table for others, this action does nothing
      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $myKeeper = $args["myKeeper"];
    $otherKeeper = $args["otherKeeper"];

    $other_player_id = $otherKeeper["location_arg"];

    if (
      $myKeeper["location"] != "keepers" ||
      $myKeeper["type"] != "keeper" ||
      $otherKeeper["location"] != "keepers" ||
      $otherKeeper["type"] != "keeper" ||
      $myKeeper["location_arg"] != $player_id ||
      $other_player_id == $player_id
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select exactly 2 Keeper cards, 1 of yours and 1 of another player"
        )
      );
    }

    // switch the keeper locations
    Utils::moveKeeperToPlayer($player_id, $myKeeper,
      $player_id, $other_player_id, "");
    Utils::moveKeeperToPlayer($player_id, $otherKeeper,
      $other_player_id, $player_id, "");

    // extra notification about the switch
    $players = $game->loadPlayersBasicInfos();
    $other_player_name = $players[$other_player_id]["player_name"];
    $myKeeperCard = $game->getCardDefinitionFor($myKeeper);
    $otherKeeperCard = $game->getCardDefinitionFor($otherKeeper);

    $game->notifyAllPlayers(
      "actionResolved",
      clienttranslate(
        '${player_name} got <b>${other_keeper_name}</b> from ${player_name2} in exchange for <b>${my_keeper_name}</b>'
      ),
      [
        "i18n" => ["other_keeper_name", "my_keeper_name"],
        "player_name" => $game->getActivePlayerName(),
        "player_name2" => $other_player_name,
        "other_keeper_name" => $otherKeeperCard->getName(),
        "my_keeper_name" => $myKeeperCard->getName(),
      ]
    );
  }
}
