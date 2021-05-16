<?php
namespace StarFluxx\Cards\Keepers;

use StarFluxx\Game\Utils;

class KeeperUnseenForce extends KeeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Unseen Force");

    $this->description = clienttranslate("Once during your turn, you can steal a card chosen randomly from another player's hand and add that card to your own hand.");
  }

  public function canBeUsedInPlayerTurn($player_id)
  {
    $alreadyUsed = !Utils::playerHasNotYetUsedUnseenForce();
    if ($alreadyUsed) return false;

    return true;
  }

  public $interactionNeeded = "playerSelection";

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $game->setGameStateValue("playerTurnUsedUnseenForce", 1);

    $players = $game->loadPlayersBasicInfos();
    $player_name = $game->getActivePlayerName();
    $selected_player_id = $args["selected_player_id"];
    $selected_player_name = $players[$selected_player_id]["player_name"];

    $cards = $game->cards->getCardsInLocation("hand", $selected_player_id);
    $cardsCount = count($cards);

    if ($cardsCount == 0) {
      // No card to steal, nothing to do
      return null;
    }

    $i = bga_rand(0, $cardsCount - 1);
    $card = array_values($cards)[$i];
    $card_definition = $game->getCardDefinitionFor($card);

    $game->notifyPlayer($selected_player_id, "cardsSentToPlayer", "", [
      "cards" => [$card],
      "player_id" => $player_id,
    ]);
    $game->notifyPlayer($player_id, "cardsReceivedFromPlayer", "", [
      "cards" => [$card],
      "player_id" => $selected_player_id,
    ]);
    $game->notifyAllPlayers(
      "actionDone",
      clienttranslate(
        '${player_name} uses <b>${this_name}</b> to steal a card from ${player_name2}\'s hand'
      ),
      [
        "i18n" => ["this_name"],
        "this_name" => $this->getName(),
        "player_name" => $player_name,
        "player_name2" => $selected_player_name,
      ]
    );
    $game->sendHandCountNotifications();

    // We move this card in the player's hand
    $game->cards->moveCard($card["id"], "hand", $player_id);
  }
}
