<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionTimePortal extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Time Portal");
    $this->description = clienttranslate(
      "Pick up either the discard pile (the Past) or the draw pile (the Future) and choose any non-Creeper card you wish. Leave the order unchanged for the Past, and re-shuffle if you visit the Future. After revealing what you selected, the card goes into your hand and your turn ends immediately."
    );
    
    $this->interactionNeeded = $this->interactionNeeded1;
    $this->help = clienttranslate(
      "Choose to select a card from the Past (discard pile) or the Future (draw pile)."
    );    
    
    $game = Utils::getGame();
    $actionPhase = $game->getGameStateValue("tmpActionPhase");
    if ($actionPhase > 0) {
      $this->interactionNeeded = $this->interactionNeeded2;
    }
  }

  public $interactionNeeded = null;
  public $interactionNeeded1 = "buttons";
  public $interactionNeeded2 = "availableCardsSelection";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $game->setGameStateValue("tmpActionPhase", 0);

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolveArgs()
  {
    $game = Utils::getGame();
    $actionPhase = $game->getGameStateValue("tmpActionPhase");
    if ($actionPhase == 2) {
      return [
        "availableTypes" => ["keeper", "goal", "rule", "action"],
        "availableCards" => $this->getAvailableCardsFromDrawPile()
      ];
    }
    else if ($actionPhase == 1) {
      return [
        "availableTypes" => ["keeper", "goal", "rule", "action"],
        "availableCards" => $this->getAvailableCardsFromDiscardPile()
      ];
    }

    return [
      ["value" => "past", "label" => clienttranslate("The Past")],
      ["value" => "future", "label" => clienttranslate("The Future")],
    ];
  }

  private function getAvailableCardsFromDrawPile()
  {
    $game = Utils::getGame();
    $cardsInDeck = $game->cards->getCardsInLocation("deck");
    // have to remove any creepers, TimePortal itself,
    // and also exclude any "Temp Hand" cards that are still being resolved
    $tmpHand1CardUniqueId = $game->getGameStateValue("tmpHand1Card");
    $tmpHand2CardUniqueId = $game->getGameStateValue("tmpHand2Card");
    $tmpHand3CardUniqueId = $game->getGameStateValue("tmpHand3Card");
    foreach ($cardsInDeck as $card_id => $card) {
      $cardUniqueId = $card["type_arg"];
      if ($cardUniqueId == $this->getUniqueId()
          || $cardUniqueId == $tmpHand1CardUniqueId
          || $cardUniqueId == $tmpHand2CardUniqueId
          || $cardUniqueId == $tmpHand3CardUniqueId
          || $card["type"] == "creeper"
          ) {
        unset($cardsInDeck[$card["id"]]);
      }
    }

    return $cardsInDeck;
  }

  private function getAvailableCardsFromDiscardPile()
  {
    $game = Utils::getGame();
    $cardsInDiscard = $game->cards->getCardsInLocation("discard");
    // have to remove any creepers, TimePortal itself,
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
          || $card["type"] == "creeper"
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
    $secondResolve = array_key_exists("card", $args);

    if ($secondResolve) {
      // second resolving: validate selected card comes from the correct deck      
      // then move that card to player's hand and end their turn
      $card = $args["card"];      
      $cardType = $card["type"];

      if ($actionPhase == 2) {
        if (
          $card["location"] != "deck" ||
          $cardType == "creeper"
        ) {
          Utils::throwInvalidUserAction(
            starfluxx::totranslate(
              "You must select any non-creeper card from the draw pile"
            )
          );
        }
        // "play" this card so it moves from deck to discard pile, so we can handle it same way
        $game->cards->playCard($card["id"]);
        // draw pile should get re-shuffled after player looked at it
        $game->cards->shuffle("deck");
        $game->deckAutoReshuffle();
      } else {
        if (
          $card["location"] != "discard" ||
          $cardType == "creeper"
        ) {
          Utils::throwInvalidUserAction(
            starfluxx::totranslate(
              "You must select any non-creeper card from the discard pile"
            )
          );
        }
      }

      // We move the chosen card in the player's hand
      $this->putCardInPlayerHand($card, $player_id);      
      // this card itself might go back if player has the Time Traveler
      $this->putThisCardWhereItBelongs($player_id);
      // either way their turn is over
      $game->setGameStateValue("tmpActionPhase", 0);
      return "endOfTurn";      
    } else {
      $direction = $args["value"];
      // first resolving: player chose past or future
      if ($direction == "future") {
        $game->setGameStateValue("tmpActionPhase", 2);
      } else {
        $game->setGameStateValue("tmpActionPhase", 1);
      }

      $args = $this->resolveArgs();
      if (count($args["availableCards"]) == 0) {
        $game->notifyAllPlayers(
          "actionIgnored",
          clienttranslate(
            'There are no available cards in the chosen pile!'
          ), ["player_id" => $player_id]
        );
        $game->setGameStateValue("tmpActionPhase", 0);
        return null;
      }

      // keep resolving further
      $this->interactionNeeded = $this->interactionNeeded2;
      return parent::immediateEffectOnPlay($player_id);
    }    

    return null;
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
        '<b>${card_name}</b> moves into the hand of the Time Traveler'
      ),
      [
        "i18n" => ["card_name"],
        "card" => $card,
        "card_name" => $card_definition->getName(),
        "discardCount" => $game->cards->countCardInLocation("discard"),
      ]
    );
    $game->notifyPlayer($player_id, "cardsDrawn", "", [
      "cards" => [$card],
    ]);
  }

  private function putThisCardWhereItBelongs($player_id)
  {
    $game = Utils::getGame();
    // special ability when Action = Time Portal is played by owner of Time Traveler
    $keeperTimeTraveler = 21;
    $timetraveler_player = Utils::findPlayerWithKeeper($keeperTimeTraveler);
    if ($timetraveler_player == null || $timetraveler_player["player_id"] != $player_id) {
      return; // nothing else to do, Time Traveler not in play or with other player
    }
    // Time Traveler makes this go back to hand instead of to discard pile
    $card = $game->cards->getCard($this->getCardId());
    
    $this->putCardInPlayerHand($card, $player_id);
  }
}
