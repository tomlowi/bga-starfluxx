<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionWhatDoYouWant extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("What Do You Want?");
    $this->description = clienttranslate(
      "Remove any card you want from the discard pile. If the card is a... <b>Rule, Action or Surprise:</b> Play the card immediately. <b>Keeper or Goal</b>: Reveal it and add it to your hand. Your turn ends immediately. <b>Creeper</b>: Give it to another player. You choose what to attach it to if applicable."
    );

    $this->interactionNeeded = $this->interactionNeeded1;
    $this->help = clienttranslate(
      "Choose any card from the discard pile. If you take a creeper, afterwards select the player it should go to."
    ); 
    
    $game = Utils::getGame();
    $actionPhase = $game->getGameStateValue("tmpActionPhase");
    if ($actionPhase > 0) {
      $this->interactionNeeded = $this->interactionNeeded2;
    }
  }

  public $interactionNeeded = null;
  public $interactionNeeded1 = "availableCardsSelection";
  public $interactionNeeded2 = "playerSelection";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $game->setGameStateValue("tmpActionPhase", 0);
    $availableDiscards = $this->getAvailableCardsFromDiscardPile();

    if (count($availableDiscards) == 0) {
      // nothing in the discard, this action does nothing
      $game->notifyAllPlayers(
        "actionIgnored",
        clienttranslate(
          'There are no available cards in the discard pile!'
        ), ["player_id" => $player_id]
      );

      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolveArgs() 
  {
    $game = Utils::getGame();
    $actionPhase = $game->getGameStateValue("tmpActionPhase");
    if ($actionPhase > 0) {
      return [];
    }      

    $availableDiscards = $this->getAvailableCardsFromDiscardPile();

    return [
      "availableTypes" => ["keeper", "goal", "rule", "action", "creeper"],
      "availableCards" => $availableDiscards,
    ];
  }

  private function getAvailableCardsFromDiscardPile()
  {
    $game = Utils::getGame();
    $cardsInDiscard = $game->cards->getCardsInLocation("discard");
    // have to remove WhatDoYouWant itself,
    // and also exclude any "Temp Hand" cards that are still being resolved
    $tmpHand1CardUniqueId = $game->getGameStateValue("tmpHand1Card");
    $tmpHand2CardUniqueId = $game->getGameStateValue("tmpHand2Card");
    $tmpHand3CardUniqueId = $game->getGameStateValue("tmpHand3Card");
    foreach ($cardsInDiscard as $card_id => $card) {
      $cardUniqueId = $card["type_arg"];
      if ($cardUniqueId == $this->getUniqueId()
          || $cardUniqueId == $tmpHand1CardUniqueId
          || $cardUniqueId == $tmpHand2CardUniqueId
          || $cardUniqueId == $tmpHand3CardUniqueId
          ) {
        unset($cardsInDiscard[$card["id"]]);
      }
    }

    return $cardsInDiscard;
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $actionPhase = $game->getGameStateValue("tmpActionPhase");
    // second resolving: we selected a creeper the first time, and now the target player is selected
    if ($actionPhase > 0) {
      $creeper_card_id = $actionPhase;
      $selected_player_id = $args["selected_player_id"];

      $stateTransition = $this->giveCreeperToPlayer($creeper_card_id, $selected_player_id, $player_id);
      $game->setGameStateValue("tmpActionPhase", 0);

      return $stateTransition;
    }

    // default resolving: we selected a card from the discard pile that we want
    $card = $args["card"];      
    $cardType = $card["type"];

    if (
      $card["location"] != "discard"
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select any available card from the discard pile"
        )
      );
    }

    // now, depending on the type of card they wanted, execute the correct action
    $cardType = $card["type"];

    switch ($cardType) {
      // Rule, Action (or Surprise): forced play
      case "rule":
      case "action":
        $this->setCardAsForcedPlay($card, $player_id);
        break;
      // Keeper or Goal: tell everyone what it is, then add to player's hand + end turn
      case "keeper":
      case "goal":
        $this->putCardInPlayerHand($card, $player_id);
        // force end of turn,
      // but can't transition "endOfTurn" here, as other "Temp Hand" actions might still be resolving
      Utils::setForcedTurnEnd();
        return null;
      // Creeper: select another player to give it to.
      // if it needs to be resolved, actually this player should resolve instead of the receiver
      case "creeper":
        $game->setGameStateValue("tmpActionPhase", $card["id"]);
        // keep resolving further
        $this->interactionNeeded = $this->interactionNeeded2;
        return parent::immediateEffectOnPlay($player_id);
    }
  }

  private function setCardAsForcedPlay($card, $player_id)
  {
    $game = Utils::getGame();

    $game->cards->moveCard($card["id"], "hand", $player_id);

    $forcedCard = $game->getCardDefinitionFor($card);
    $game->notifyPlayer($player_id, "forcedCardNotification", "", [
      "card_trigger" => $this->getName(),
      "card_forced" => $forcedCard->getName(),
    ]);

    // And we mark it as the next "forcedCard" to play
    $game->setGameStateValue("forcedCard", $card["id"]);
  }

  private function putCardInPlayerHand($card, $player_id) 
  {
    $game = Utils::getGame();

    $game->cards->moveCard($card["id"], "hand", $player_id);
    $card_definition = $game->getCardDefinitionFor($card);

    // Then we notify players and update the discard pile
    $game->notifyAllPlayers(
      "cardTakenFromDiscard",
      clienttranslate(
        '${player_name} wants <b>${card_name}</b> from the discard pile'
      ),
      [
        "i18n" => ["card_name"],
        "card" => $card,
        "card_name" => $card_definition->getName(),
        "player_name" => Utils::getPlayerName($player_id),
        "discardCount" => $game->cards->countCardInLocation("discard"),
      ]
    );
    $game->notifyPlayer($player_id, "cardsDrawn", "", [
      "cards" => [$card],
    ]);
  }

  private function giveCreeperToPlayer($creeper_card_id, $selected_player_id, $active_player_id)
  {
    $game = Utils::getGame();
    $card = $game->cards->getCard($creeper_card_id);
    $card_definition = $game->getCardDefinitionFor($card);

    // move this creeper to the selected player hand, and play it from there
    $game->cards->moveCard($card["id"], "hand", $selected_player_id);
    // notify so card is shown in hand and can be played
    $game->notifyPlayer($selected_player_id, "cardsDrawn", "", [
      "cards" => [$card],
    ]);
    // force play the creeper for the target player
    $game->action_forced_playCard_forOtherPlayer($card["id"], $selected_player_id);

    // if creeper should be attached for the target player, the active player gets to do it
    $stateTransition = $card_definition->onCheckResolveKeepersAndCreepers($card);
    if ($stateTransition != null)
    { // overrule default behavior: this player gets to choose where to attach
      $game->setGameStateValue("creeperToResolvePlayerId", $active_player_id);
      return $stateTransition;
    }
  }
}
