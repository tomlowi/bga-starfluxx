<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionItsATrap extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("It's a Trap!");
    $this->description = clienttranslate(
      "<b>Out of turn:</b> Cancel any single game action in which another player is stealing a Keeper you have on the table, and instead you steal one of their Keepers. <b>During your turn:</b> All other players must choose a card from their hands to discard, while you draw 2.<br>This card can also cancel another Surprise."
    );

    $this->interactionOther = $this->interactionOtherDuringTurn;
    $this->help = clienttranslate(
      "Select any card to discard from your hand, or select nothing and get a random card discarded."
    );

    $game = Utils::getGame();
    if ($game->getGameStateValue("playerIdTrappedTarget") > -1)
    {
      $this->interactionOther = $this->interactionOtherAsTrap;
      $this->help = clienttranslate(
        "Select a keeper card in front of the player that was trapped."
      );
    }
  }

  public function getActionType()
  {
    return "surprise";
  }

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $this->interactionOther = $this->interactionOtherDuringTurn;

    // Draw 2 extra cards
    $extraCards = 2;
    // make sure we can't draw back this card itself (after reshuffle if deck would be empty)
    $game->cards->moveCard($this->getCardId(), "side", $player_id);

    $game->performDrawCards($player_id, $extraCards, true);

    // move this card itself back to the discard pile
    $game->cards->playCard($this->getCardId());        

    // force other players to react
    Utils::getGame()->setGameStateValue(
      "actionToResolve",
      $this->getCardId()
    );
    return "resolveActionByOthers";
  }

  public $interactionOther = null;
  public $interactionOtherDuringTurn = "handCardOptionalSelection";
  public $interactionOtherAsTrap = "keeperSelectionOther";

  public function resolvedByOther($player_id, $args)
  {
    $game = Utils::getGame();
    // Out of turn Trap: resolve = keeper stolen back
    if ($game->getGameStateValue("playerIdTrappedTarget") > -1)
    {
      $this->resolvedAsOutOfTurnTrap($player_id, $args);
      return;
    }

    // During turn: normal resolve = other players discarding a card
    // if a card was selected, validate it is any card in hand of player and discard
    // if not, select any random card from player hand and discard that
    $card = $args["card"];

    if ($card != null && (
      $card["location"] != "hand" ||
      $card["location_arg"] != $player_id
      )
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must discard any card from your hand"
        )
      );
    }    

    if ($card == null) {
      $handCards = $game->cards->getCardsInLocation("hand", $player_id);
      $handCardsCount = count($handCards);
      if ($handCardsCount > 0) {
        $i = bga_rand(0, $handCardsCount - 1);
        $card = array_values($handCards)[$i];
      }
    }
    // no hand cards > nothing to discard
    if ($card == null) {
      return;
    }
    
    // discard the selected card from hand
    $game->cards->playCard($card["id"]);

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $player_id,
      "cards" => [$card],
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $player_id),
    ]);
  }

  public function outOfTurnCounterPlay($surpriseTargetId)
  {
    $game = Utils::getGame();
    $this->interactionOther = $this->interactionOtherAsTrap;

    $surpriseCounterId = $this->getCardId();

    $targetCard = $game->cards->getCard($surpriseTargetId);
    $targetPlayerId = $targetCard["location_arg"];
    $surpriseCard = $game->cards->getCard($surpriseCounterId);
    $surpriseCard["location_arg"] = $surpriseCard["location_arg"]  % OFFSET_PLAYER_LOCATION_ARG;
    $surprisePlayerId = $surpriseCard["location_arg"];

    if ($targetCard["type"] == "action")
    {
      $game->cards->playCard($surpriseTargetId);
      // Cancel the Action played => discard it, and discard this card    
      $discardCount = $game->cards->countCardInLocation("discard");
      $game->notifyAllPlayers("handDiscarded", "", [
        "player_id" => $targetPlayerId,
        "cards" => [$targetCard],
        "discardCount" => $discardCount,
        "handCount" => $game->cards->countCardInLocation("hand", $targetPlayerId),
      ]);      
    } 
    else if ($targetCard["type"] == "keeper")
    {
      // this was the keeper stolen from us: take it back
      $notificationMsg = clienttranslate(
        '${player_name} retrieves <b>${card_name}</b>'
      );
      Utils::moveKeeperToPlayer($surprisePlayerId, $targetCard,
        $targetPlayerId, $surprisePlayerId, $notificationMsg, false);
    }
    
    $game->cards->playCard($surpriseCounterId);

    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $surprisePlayerId,
      "cards" => [$surpriseCard],
      "discardCount" => $game->cards->countCardInLocation("discard"),
      "handCount" => $game->cards->countCardInLocation("hand", $surprisePlayerId),
    ]);

    // Steal a Keeper from the player that tried to steal from you
    // That is either the active Player, or the player with Teleporter (for BeamUsUp)!
    // see https://faq.looneylabs.com/question/1621
    $targetKeeperCount = Utils::getPlayerKeeperCount($targetPlayerId);
    if ($targetKeeperCount == 0)
    {
      $players = $game->loadPlayersBasicInfos();
      $game->notifyAllPlayers(
        "actionDone",
        clienttranslate('${player_name} has no other keepers to trap'),
        [
          "player_name" => $players[$targetPlayerId]["player_name"],
        ]
      );
      return null;
    }

    // track which player we should steal back from
    $game->setGameStateValue("playerIdTrapper", $surprisePlayerId);
    $game->setGameStateValue("playerIdTrappedTarget", $targetPlayerId);
    $game->setGameStateValue("actionToResolve", $this->getCardId());    
    return "resolveActionByOthers";
  }

  public function resolvedAsOutOfTurnTrap($player_id, $args)
  {
    $game = Utils::getGame();

    $card = $args["card"];
    $card_definition = $game->getCardDefinitionFor($card);

    $card_type = $card["type"];
    $card_location = $card["location"];
    $other_player_id = $card["location_arg"];

    $targetPlayerId = $game->getGameStateValue("playerIdTrappedTarget");

    self::dump("===TRAP===", [
      "args" => $args,
      "card" => $card,
      "target" => $targetPlayerId,
    ]);

    if (
      $card_type != "keeper" ||
      $card_location != "keepers" ||
      $other_player_id != $targetPlayerId
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper card in front of the trapped player"
        )
      );
    }

    // move this keeper to the current player
    $notificationMsg = clienttranslate(
      '${player_name} trapped <b>${card_name}</b> from ${player_name2}'
    );
    Utils::moveKeeperToPlayer($player_id, $card,
      $other_player_id, $player_id, $notificationMsg);

    $game->setGameStateValue("playerIdTrapper", -1);
    $game->setGameStateValue("playerIdTrappedTarget", -1);
  }

}
