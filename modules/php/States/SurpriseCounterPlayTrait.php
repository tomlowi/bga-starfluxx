<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use starfluxx;

trait SurpriseCounterPlayTrait
{
  private function getSurpriseTarget()
  {
    return self::getGameStateValue("cardIdSurpriseTarget");
  }

  public function st_allowSurpriseCounterPlay()
  {
    $playersForSurprise = self::loadPlayersBasicInfos();
    // Only other players can Suprise the card played by the active player.
    $active_player_id = self::getActivePlayerId();

    if (array_key_exists($active_player_id, $playersForSurprise)) {
      unset($playersForSurprise[$active_player_id]);
    }

    $gamestate = Utils::getGame()->gamestate;

    // Activate all players that might choose to Surprise counter the card played
    $stateTransition = "surprisePlayChecked";
    if (empty($playersForSurprise)) {
      $gamestate->setAllPlayersNonMultiactive($stateTransition);
    } else {
      $gamestate->setPlayersMultiactive(array_keys($playersForSurprise), $stateTransition, true);
    }  
  }

  public function arg_allowSurpriseCounterPlay()
  {
    $game = Utils::getGame();

    $targetCardId = $this->getSurpriseTarget();
    $card = $game->cards->getCard($targetCardId);
    $card_definition = $game->getCardDefinitionFor($card);

    return [
      "i18n" => ["playedCardName"],
      "playedCardName" => $card_definition->getName(),
      "surpriseCards" => [$card],
    ];
  }

  /*
   * Current Player decides to Surprise counter (if they can) or not
   */
  function action_decideSurpriseCounterPlay($card_id)
  {
    $game = Utils::getGame();

    $stateTransition = "surprisePlayChecked";
    
    $player_id = self::getCurrentPlayerId();
    
    if ($card_id != null)
    {
      // the Surprise card used must be in player's hand
      $handCards = $game->cards->getCardsInLocation("hand", $player_id);
      if (!array_key_exists($card_id, $handCards)) {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate("You do not have this card in hand")
        );
      }
      // Validate this card is a Surprise that can be used on the played card
      $target_card_id = self::getGameStateValue("cardIdSurpriseTarget");
      if (!$this->checkCardIsValidSurpriseCounterFor($card_id, $target_card_id))
      {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate("This is not a valid Surprise counter action")
        );
      }
      self::setGameStateValue("cardIdSurpriseCounter", $card_id);

      // move card to surprises queue, but keep it registered for this player
      $game->cards->moveCard($card_id, "surprises", $player_id);

      // check if other players have more Surprises that might cancel this
      if (Utils::otherPlayersWithSurpriseInHand($player_id)) {

        $players = $game->loadPlayersBasicInfos();
        $surpriseCard = $game->cards->getCard($card_id);

        $game->notifyAllPlayers("surprise", 
        clienttranslate('${player_name} adds <b>${card_surprise}</b> to the Surprise queue'),
        [
          "i18n" => ["card_surprise"],
          "player_name" => $players[$player_id]["player_name"],
          "card_surprise" => $game->getCardDefinitionFor($surpriseCard)->getName(),
        ]);

        $stateTransition = "checkForSurpriseCancels";
      }

      // this player decided to play Surprise already: all others can be skipped
      $game->gamestate->setAllPlayersNonMultiactive($stateTransition);
    }
    // else: current player doesn't have Surprise or decided not to play it    
    $game->gamestate->setPlayerNonMultiactive($player_id, $stateTransition);
  }

  private function checkCardIsValidSurpriseCounterFor($surprise_card_id, $target_card_id)
  {
    $game = Utils::getGame();
    $surprise_card = $game->cards->getCard($surprise_card_id);
    $surprise_card_def = $game->getCardDefinitionFor($surprise_card);

    if ($surprise_card["type"] != "action"
      || $surprise_card_def->getActionType() != "surprise") {
        return false;
      }

    $target_card = $game->cards->getCard($target_card_id);
    $target_type = $target_card["type"];
    $target_unique = $target_card["type_arg"];

    $surprise_player_id = $surprise_card["location_arg"];
    
    $valid_surprise = false;
    switch ($target_type)
    {
      case "keeper":
        // That's Mine = 318
        $valid_surprise = $surprise_card_def->getUniqueId() == 318;
        break;
      case "goal":
        // Canceled Plans = 321
        $valid_surprise = $surprise_card_def->getUniqueId() == 321;
        break;
      case "rule":
        // Veto = 319
        $valid_surprise = $surprise_card_def->getUniqueId() == 319;
        break;
      case "action":
        // BelayThat = 320
        $valid_surprise = $surprise_card_def->getUniqueId() == 320
        // or It's A Trap = 317 sometimes can also be used against BeamUsUp = 311 action        
          || ($surprise_card_def->getUniqueId() == 317 && $target_unique == 311
              && Utils::checkBeamUsUpCouldTeleportBeingsFrom($surprise_player_id))
          ;
        break;
    }

    return $valid_surprise;
  }
}
