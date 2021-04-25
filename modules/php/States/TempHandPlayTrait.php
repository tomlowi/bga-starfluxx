<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Actions\ActionCardFactory;
use starfluxx;

trait TempHandPlayTrait
{
  public function st_tempHandPlay()
  {
  }

  public function arg_tempHandPlay()
  {
    $game = Utils::getGame();
    $player_id = $game->getActivePlayerId();

    // check game states for which temp hands are active
    // and how many cards must still be played for the active temp hand
    $tmpHandActive = Utils::getActiveTempHand();
    $tmpHandLocation = "tmpHand" . $tmpHandActive;
    $tmpToPlay = $game->getGameStateValue($tmpHandLocation . "ToPlay");

    // get cards for all temp hand locations for this player
    $tmpHands = [];
    $tmpHandName = null;
    for ($i = 1; $i <= 3; $i++) {
      $tmpHandNext = "tmpHand" . $i;
      $tmpCardArg = $game->getGameStateValue($tmpHandNext . "Card");
      if ($tmpCardArg > 0) {
        $tmpHandName = ActionCardFactory::getCard(0, $tmpCardArg)->getName();
        $tmpHandCards = $this->cards->getCardsInLocation(
          $tmpHandNext,
          $player_id
        );

        $tmpHands[$tmpHandNext] = [
          "tmpHandName" => $tmpHandName,
          "tmpHandCards" => $tmpHandCards,
        ];
      }
    }

    return [
      "i18n" => ["tmpHandName"],
      "tmpHandActive" => $tmpHandLocation,
      "tmpHandName" => $tmpHandName,
      "tmpToPlay" => $tmpToPlay,
      "tmpHands" => $tmpHands,
    ];
  }

  public function action_selectTempHandCard($card_id)
  {
    // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
    $game = Utils::getGame();
    $game->checkAction("selectTempHandCard");

    $tmpHandActive = Utils::getActiveTempHand();
    $tmpHandLocation = "tmpHand" . $tmpHandActive;
    // verify this card comes from the correct player temp hand
    $player_id = $game->getActivePlayerId();
    $card = $game->cards->getCard($card_id);

    if (
      $card["location"] != $tmpHandLocation or
      $card["location_arg"] != $player_id
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate("You do not have this card in that temporary hand")
      );
    }

    // resolved 1 temp hand play
    $game->incGameStateValue($tmpHandLocation . "ToPlay", -1);

    // We move this card in the player's hand
    $game->cards->moveCard($card["id"], "hand", $player_id);

    // And we mark it as the next "forcedCard"
    $game->setGameStateValue("forcedCard", $card["id"]);

    // which allows us to continue to normal play state
    $game->gamestate->nextstate("selectedCard");
  }
}
