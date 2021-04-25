<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionWhatDoYouWant extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Use What You Take");
    $this->description = clienttranslate(
      "Take a card at random from another player's hand, and play it."
    );

    $this->help = clienttranslate(
      "Choose the player you want to take a card from (that will be played automatically)."
    );
  }

  public $interactionNeeded = "playerSelection";

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
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
        '${player_name} took ${card_name} from ${player_name2}\'s hand (and must play it)'
      ),
      [
        "i18n" => ["card_name"],
        "card_name" => $card_definition->getName(),
        "player_name" => $player_name,
        "player_name2" => $selected_player_name,
      ]
    );
    $game->sendHandCountNotifications();

    // We move this card in the player's hand
    $game->cards->moveCard($card["id"], "hand", $player_id);

    $forcedCard = $game->getCardDefinitionFor($card);
    $game->notifyPlayer($player_id, "forcedCardNotification", "", [
      "card_trigger" => $this->getName(),
      "card_forced" => $forcedCard->getName(),
    ]);

    // And we mark it as the next "forcedCard"
    $game->setGameStateValue("forcedCard", $card["id"]);
  }
}
