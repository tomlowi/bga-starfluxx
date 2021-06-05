<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionBrainTransference extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Brain Transference");
    $this->description = clienttranslate(
      // Original card text is not suitable on BGA platform, as discussed with publisher we will change this card
      // to simply transfer all hand cards and everything in play between the 2 players.
      //"You and a player of your choice switch seats at the table (leaving your hands). You each take over the other player's entire position in the game, as if you were in that position all along. Your turn ends immediately. The person next in turn order from your original seat goes next unless that's you, in which case the player after your new seat goes next."
      "You and a player of your choice switch everything that you have in play. Transfer all hand cards, keepers and creepers, as if you were in that position all along. Your turn ends immediately."
    );

    $this->help = clienttranslate(
      "Choose the player you want to transfer brains with."
    );
  }

  public $interactionNeeded = "playerSelection";


  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $players = $game->loadPlayersBasicInfos();

    $player_name = $players[$player_id]["player_name"];
    $selected_player_id = $args["selected_player_id"];
    $selected_player_name = $players[$selected_player_id]["player_name"];

    $game->notifyAllPlayers(
      "actionDone",
      clienttranslate('${player_name} transfers brains with ${player_name2}'),
      [
        "player_name" => $player_name,
        "player_name2" => $selected_player_name,
      ]
    );

    // first transfer all hand cards
    $selected_player_hand = $game->cards->getCardsInLocation(
      "hand",
      $selected_player_id
    );
    $player_hand = $game->cards->getCardsInLocation("hand", $player_id);

    $game->cards->moveCards(
      array_keys($selected_player_hand),
      "hand",
      $player_id
    );
    $game->cards->moveCards(
      array_keys($player_hand),
      "hand",
      $selected_player_id
    );
    $game->notifyPlayer($player_id, "cardsSentToPlayer", "", [
      "cards" => $player_hand,
      "player_id" => $selected_player_id,
    ]);
    $game->notifyPlayer($player_id, "cardsReceivedFromPlayer", "", [
      "cards" => $selected_player_hand,
      "player_id" => $selected_player_id,
    ]);
    $game->notifyPlayer($selected_player_id, "cardsSentToPlayer", "", [
      "cards" => $selected_player_hand,
      "player_id" => $player_id,
    ]);
    $game->notifyPlayer($selected_player_id, "cardsReceivedFromPlayer", "", [
      "cards" => $player_hand,
      "player_id" => $player_id,
    ]);

    $game->sendHandCountNotifications();

    // then also transfer all keepers/creepers in play
    $selected_player_keepers = $game->cards->getCardsInLocation(
      "keepers",
      $selected_player_id
    );
    $player_keepers = $game->cards->getCardsInLocation("keepers", $player_id);

    $this->moveAllKeeperCardsToPlayer($selected_player_keepers, $player_id,
        $selected_player_id, $player_id);

    $this->moveAllKeeperCardsToPlayer($player_keepers, $player_id,
        $player_id, $selected_player_id);

    // force hand limit checks, but then also need to force end of turn
    $game->setGameStateValue("forcedTurnEnd", 1);
    return "handsExchangeOccured";
  }

  private function moveAllKeeperCardsToPlayer($cards, $active_player_id, $origin_player_id, $destination_player_id)
  {
    $game = Utils::getGame();
    foreach ($cards as $card_id => $card) {
      // attached creepers will already move automatically together with their keepers
      // so we shouldn't move these creepers here
      if ($card["type"] == "creeper") {
        $card_definition = $game->getCardDefinitionFor($card);
        if ($card_definition->isAttachedTo() > -1)
          continue;
      }

      Utils::moveKeeperToPlayer($active_player_id, $card,
        $origin_player_id, $destination_player_id, "", false);
    }
  }
}
