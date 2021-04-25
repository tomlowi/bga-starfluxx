<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;

class ActionItsATrap extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Random Tax");
    $this->description = clienttranslate(
      "Take 1 card at random from the hand of each other player and add these cards to your own hand."
    );
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();

    $player_name = $game->getActivePlayerName();

    $players = $game->loadPlayersBasicInfos();

    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $taxCount = 1 + $addInflation;

    for ($i = 0; $i < $taxCount; $i++) {
      $this->taxOtherPlayers($player_id, $player_name, $players);
    }

    $game->sendHandCountNotifications();
  }

  private function taxOtherPlayers($player_id, $player_name, $players)
  {
    $game = Utils::getGame();
    foreach ($players as $from_player_id => $from_player) {
      if ($from_player_id != $player_id) {
        $cards = $game->cards->getCardsInLocation("hand", $from_player_id);
        $cardsCount = count($cards);

        if ($cardsCount > 0) {
          $i = bga_rand(0, $cardsCount - 1);
          $card = array_values($cards)[$i];
          $card_definition = $game->getCardDefinitionFor($card);
          $game->cards->moveCard($card["id"], "hand", $player_id);
          $game->notifyPlayer(
            $player_id,
            "cardsReceivedFromPlayer",
            clienttranslate(
              '<b>You</b> received <b>${card_name}</b> from ${player_name}'
            ),
            [
              "i18n" => ["card_name"],
              "cards" => [$card],
              "card_name" => $card_definition->getName(),
              "player_id" => $from_player_id,
              "player_name" => $from_player["player_name"],
            ]
          );
          $game->notifyPlayer(
            $from_player_id,
            "cardsSentToPlayer",
            clienttranslate(
              '${player_name} took <b>${card_name}</b> from your hand'
            ),
            [
              "i18n" => ["card_name"],
              "cards" => [$card],
              "card_name" => $card_definition->getName(),
              "player_id" => $player_id,
              "player_name" => $player_name,
            ]
          );
        }
      }
    }
  }
}
