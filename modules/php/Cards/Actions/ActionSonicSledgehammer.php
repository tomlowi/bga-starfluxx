<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionSonicSledgehammer extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Sonic Sledgehammer");
    $this->description = clienttranslate(
      "All players, including you, must discard a Keeper from the table. You decide which Keeper each player discards. Players with no Keepers in play must discard a random card from their hands. If someone has the Time Traveler on the table, that player discards nothing."
    );

    $this->help = clienttranslate(
      "Select exactly 1 Keeper to discard from every player (including yourself)."
    );

    $this->keeperTimeTraveler = 21;
  }

  public $interactionNeeded = "keeperFromEachPlayer";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $game = Utils::getGame();
    $totalKeepersInPlay = count(
      $game->cards->getCardsOfTypeInLocation("keeper", null, "keepers", null)
    );
    // if nobody has keepers in play, we can skip directly to resolving without any selected cards
    // (all players will just discard a random card from hand)
    if ($totalKeepersInPlay == 0) {
      $this->resolvedBy($player_id, 
      [ 
        "cards" => [] 
      ]);
      return null;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $cards = $args["cards"];

    // group the selected cards per owning player
    $players = $game->loadPlayersBasicInfos();
    $player_selected_cards = [];
    foreach ($players as $from_player_id => $from_player) {
      $player_selected_cards[$from_player_id] = [];
    }

    foreach ($cards as $card_id => $card) {
      $card_owner = $card["location_arg"];
      $player_selected_cards[$card_owner][] = $card;
    }

    // check if anybody has the Time Traveler in play
    $timeTraveler_player_id = null;
    $player_with_timeTraveler = Utils::findPlayerWithKeeper($this->keeperTimeTraveler);
    if ($player_with_timeTraveler != null) {
      $timeTraveler_player_id = $player_with_timeTraveler["player_id"];
    }
    // validate that for every player with keepers, there is exactly 1 keeper selected
    // (unless that player has Time Traveler)
    $players = $game->loadPlayersBasicInfos();
    foreach ($players as $from_player_id => $from_player) {
      if ($timeTraveler_player_id != null && $timeTraveler_player_id == $from_player_id)
        continue;

      $playersKeepersInPlay = count(
        $game->cards->getCardsOfTypeInLocation(
          "keeper",
          null,
          "keepers",
          $from_player_id
        )
      );

      if ($playersKeepersInPlay > 0 && count($player_selected_cards[$from_player_id]) != 1
          || $player_selected_cards[$from_player_id][0]["type"] != "keeper") {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate(
            "You must select exactly 1 Keeper card from each player"
          )
        );
      }
      
    }

    // execute and notify about the results of the Sonic Sledgehammer on each player
    $trigger_name = $this->getName();
    $notificationMsg = clienttranslate(
      '${player_name} uses <b>${trigger_name}</b> on <b>${card_name}</b> from ${player_name2}'
    );
    // discard the selected keepers, or discard a random card for players without keepers
    foreach ($player_selected_cards as $from_player_id => $discards_for_player) {
      if ($timeTraveler_player_id != null && $timeTraveler_player_id == $from_player_id) {
        $game->notifyAllPlayers(
          "actionResolved",
          clienttranslate(
            '${player_name} ignores <b>${card_name}</b> because they have the Time Traveler'
          ),
          [
            "i18n" => ["card_name"],
            "player_name" => $players[$from_player_id]["player_name"],
            "card_name" => $trigger_name,
          ]
        );
        continue;
      }        

      foreach ($discards_for_player as $card_id => $card) {
        Utils::discardKeeperFromPlay($player_id, $card,
          $from_player_id, $trigger_name, $notificationMsg);
      }

      if (count($discards_for_player) == 0) {
        $discardFromHand = null;
        $handcards = $game->cards->getCardsInLocation("hand", $from_player_id);
        $cardsCount = count($handcards);
        if ($cardsCount > 0) {
          $i = bga_rand(0, $cardsCount - 1);
          $discardFromHand = array_values($handcards)[$i];
        }

        if ($discardFromHand != null)
        {
          $cards = $game->discardCardsFromLocation(
            [ $discardFromHand["id"] ],
            "hand",
            $from_player_id,
            null
          );
  
          $game->notifyAllPlayers(
            "actionResolved",
            clienttranslate(
              '${player_name} uses <b>${card_name}</b> to discard a random hand card from ${player_name2}'
            ),
            [
              "i18n" => ["card_name"],
              "player_name" => $players[$player_id]["player_name"],
              "player_name2" => $players[$from_player_id]["player_name"],
              "card_name" => $trigger_name,
            ]
          );
      
          $game->notifyAllPlayers("handDiscarded", "", [
            "player_id" => $from_player_id,
            "cards" => $cards,
            "discardCount" => $game->cards->countCardInLocation("discard"),
            "handCount" => $game->cards->countCardInLocation("hand", $from_player_id),
          ]);
        }
      }
    }
  }
}
