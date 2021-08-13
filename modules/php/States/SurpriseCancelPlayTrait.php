<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use starfluxx;

trait SurpriseCancelPlayTrait
{
  public function st_allowSurpriseCancelSurprise()
  {
    $game = Utils::getGame();
    $gamestate = Utils::getGame()->gamestate;

    // Only other players can Cancel the last Surprise card played
    $last_surprise_player_id = self::getActivePlayerId();

    $surprise_cards = $game->cards->getCardsInLocation("surprises", null, "location_arg");
    $last_surprise = array_pop($surprise_cards);
    if ($last_surprise != null) {
      $last_surprise_player_id = $last_surprise["location_arg"] % OFFSET_PLAYER_LOCATION_ARG;
    }

    $playersForSurprise = Utils::listPlayersWithSurpriseInHandFor($last_surprise);

    if (array_key_exists($last_surprise_player_id, $playersForSurprise)) {
      unset($playersForSurprise[$last_surprise_player_id]);
    }

    // Activate all players that might choose to Surprise cancel the previous Surprise
    $stateTransition = "surpriseCancelChecked";
    if (empty($playersForSurprise)) {
      $gamestate->setAllPlayersNonMultiactive($stateTransition);
    } else {
      $gamestate->setPlayersMultiactive(array_keys($playersForSurprise), $stateTransition, true);
    }  
  }

  public function arg_allowSurpriseCancelSurprise()
  {
    $game = Utils::getGame();

    $targetCardId = $this->getSurpriseTarget();
    $card = $game->cards->getCard($targetCardId);
    $card_definition = $game->getCardDefinitionFor($card);

    // include all cards in the Surprise Queue
    $surprise_cards = $game->cards->getCardsInLocation("surprises", null, "location_arg");
    array_unshift($surprise_cards, $card);

    return [
      "i18n" => ["playedCardName"],
      "playedCardName" => $card_definition->getName(),
      "surpriseCards" => $surprise_cards,
    ];
  }

  /*
   * Current Player decides to Surprise cancel (if they can) or not
   */
  function action_decideSurpriseCancelSurprise($card_id)
  {
    $game = Utils::getGame();

    $stateTransition = "surpriseCancelChecked";
    
    $player_id = self::getCurrentPlayerId();
    
    if ($card_id != null)
    {
      // the Surprise card used must be in player's hand
      $handCards = $game->cards->getCardsInLocation("hand", $player_id);

      self::dump("===OHOH===", [
        "player" => $player_id,
        "hands" => $handCards,
        "card" => $card_id,
      ]);

      if (!array_key_exists($card_id, $handCards)) {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate("You do not have this card in hand")
        );
      }
      // Validate this card is a Surprise that can be used on the played card
      if (!$this->checkCardIsValidSurpriseCancel($card_id))
      {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate("This is not a valid Surprise cancel action")
        );
      }
      // move card to surprises queue, but keep it registered for this player
      $countPrefix = 1+$game->cards->countCardsInLocation("surprises");
      // we need location_arg to keep ordered queue, but still be able to determine the player from it
      // HACK here because deck component uses location_arg differently for
      // "hand" style locations and "pile" style locations, here we want a bit of both
      $game->cards->insertCard($card_id, "surprises", ($countPrefix * OFFSET_PLAYER_LOCATION_ARG) + $player_id);

      // notification
      $players = $game->loadPlayersBasicInfos();
      $surpriseCard = $game->cards->getCard($card_id);

      $game->notifyAllPlayers("surprise", 
      clienttranslate('${player_name} adds <b>${card_surprise}</b> to the Surprise queue'),
      [
        "i18n" => ["card_surprise"],
        "player_name" => $players[$player_id]["player_name"],
        "card_surprise" => $game->getCardDefinitionFor($surpriseCard)->getName(),
      ]);

      // check if other players have more Surprises that might cancel this
      if (Utils::otherPlayersWithSurpriseInHand($player_id)) {
        $stateTransition = "checkForSurpriseCancels";
      }

      // this player decided to play Surprise already: all others can be skipped
      $game->gamestate->setAllPlayersNonMultiactive($stateTransition);
    }
    else
    {
      // else: current player doesn't have Surprise or decided not to play it    
      $game->gamestate->setPlayerNonMultiactive($player_id, $stateTransition);
    }
  }

  private function checkCardIsValidSurpriseCancel($surprise_card_id)
  {
    $game = Utils::getGame();
    $surprise_card = $game->cards->getCard($surprise_card_id);
    $surprise_card_def = $game->getCardDefinitionFor($surprise_card);

    if ($surprise_card["type"] != "action"
      || $surprise_card_def->getActionType() != "surprise") {
        return false;
      }

    return true;
  }
}
