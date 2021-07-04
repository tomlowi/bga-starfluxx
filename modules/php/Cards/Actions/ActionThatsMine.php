<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionThatsMine extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("That's Mine!");
    $this->description = clienttranslate(
      "<b>Out of turn:</b> When another player plays a Keeper, it goes in front of you instead of them, possibly preventing their victory. <b>During your turn:</b> Steal another player's Keeper and put it in front of you.<br>This card can also cancel another Surprise."
    );

    $this->help = clienttranslate(
      "Select any keeper card in play from another player."
    );
  }

  public function getActionType()
  {
    return "surprise";
  }

  public function outOfTurnCounterPlay($surpriseTargetId)
  {
    $game = Utils::getGame();

    $surpriseCounterId = $this->getCardId();

    $targetCard = $game->cards->getCard($surpriseTargetId);
    $targetPlayerId = $targetCard["location_arg"];
    $surpriseCard = $game->cards->getCard($surpriseCounterId);
    $surprisePlayerId = $surpriseCard["location_arg"];

    // Intercept the Keeper played, goes to surprise player instead of original player, then discard this card

    // so, first transfer the Keeper from playing hand to surprise player hand
    $game->notifyPlayer($targetPlayerId, "cardsSentToPlayer", "", [
      "cards" => [$targetCard],
      "player_id" => $surprisePlayerId,
    ]);
    $game->notifyPlayer($surprisePlayerId, "cardsReceivedFromPlayer", "", [
      "cards" => [$targetCard],
      "player_id" => $targetPlayerId,
    ]);
    $game->sendHandCountNotifications();
    // then play it as normal
    $game->playKeeperCard($surprisePlayerId, $targetCard);

    // finally discard the surprise card
    $game->cards->playCard($surpriseCounterId);
    
    $discardCount =$game->cards->countCardInLocation("discard");
    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $surprisePlayerId,
      "cards" => [$surpriseCard],
      "discardCount" => $discardCount,
      "handCount" => $game->cards->countCardInLocation("hand", $surprisePlayerId),
    ]); 
  }

  public $interactionNeeded = "keeperSelectionOther";

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
    if ($totalKeepersInPlay - $playersKeepersInPlay == 0) {
      // no keepers on the table for others, this action does nothing
      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $card = $args["card"];
    $card_definition = $game->getCardDefinitionFor($card);

    $card_type = $card["type"];
    $card_location = $card["location"];
    $other_player_id = $card["location_arg"];

    if (
      $card_type != "keeper" ||
      $card_location != "keepers" ||
      $other_player_id == $player_id
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper card in front of another player"
        )
      );
    }

    // move this keeper to the current player
    $notificationMsg = clienttranslate(
      '${player_name} stole <b>${card_name}</b> from ${player_name1}'
    );
    Utils::moveKeeperToPlayer($player_id, $card,
      $other_player_id, $player_id, $notificationMsg);    
  }  
}
