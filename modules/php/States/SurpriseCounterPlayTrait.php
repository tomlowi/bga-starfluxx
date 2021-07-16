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
    $playersForSuprise = self::loadPlayersBasicInfos();
    // Only other players can Suprise the card played by the active player.
    $active_player_id = self::getActivePlayerId();

    if (array_key_exists($active_player_id, $playersForSuprise)) {
      unset($playersForSuprise[$active_player_id]);
    }

    $gamestate = Utils::getGame()->gamestate;

    // Activate all players that might choose to Surprise counter the card played
    $stateTransition = "surprisePlayChecked";
    if (empty($playersForSuprise)) {
      $gamestate->setAllPlayersNonMultiactive($stateTransition);
    } else {
      $gamestate->setPlayersMultiactive(array_keys($playersForSuprise), $stateTransition, true);
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

      // check if other players have more Surprises that might cancel this
      if (Utils::otherPlayersWithSurpriseInHand($player_id)) {
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
        $valid_surprise = $surprise_card_def->getUniqueId() == 320;
        break;
    }

    return $valid_surprise;
  }
}
