<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use starfluxx;

class ActionSonicSledgehammer extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Rock-Paper-Scissors Showdown");
    $this->description = clienttranslate(
      "Challenge another player to a 3-round Rock-Paper-Scissors tournament. Winner takes loser's entire hand of cards."
    );

    $this->help = clienttranslate("Choose the player you want to challenge.");
  }

  public $interactionNeeded = "playerSelection";

  public function immediateEffectOnPlay($player_id)
  {
    // nothing now, needs to go to resolve action state
    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolvedBy($player_id, $args)
  {
    //$choice = $args["value"];
    $game = Utils::getGame();
    $selected_player_id = $args["selected_player_id"];

    $game->setGameStateValue("rpsChallengerId", $player_id);
    $game->setGameStateValue("rpsDefenderId", $selected_player_id);
    $game->setGameStateValue("rpsChallengerChoice", -1);
    $game->setGameStateValue("rpsDefenderChoice", -1);
    $game->setGameStateValue("rpsChallengerWins", 0);
    $game->setGameStateValue("rpsDefenderWins", 0);

    $cardsInHandChallenger = $game->cards->countCardInLocation("hand", $player_id);
    $cardsInHandDefender = $game->cards->countCardInLocation("hand", $selected_player_id);

    if ($cardsInHandChallenger == 0 && $cardsInHandDefender == 0) {
      // both player have no cards in hand, no need to showdown
      $game->notifyAllPlayers(
        "actionIgnored",
        clienttranslate(
          'Both players are empty-handed, no need for Rock-Paper-Scissors showdown'
        ), ["player_id" => $player_id]
      );
      return "resolvedAction";
    }    

    return "playRockPaperScissors";
  }
}
