<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;

if (!defined("RPS_OPTION_ROCK")) {
  define("RPS_OPTION_ROCK", 1);
  define("RPS_OPTION_PAPER", 2);
  define("RPS_OPTION_SCISSORS", 3);
}

trait RockPaperScissorsTrait
{
  private function getWinnerChoice($challenger, $defender)
  {
    // Same choice: no winner
    if ($challenger == $defender) {
      return null;
    }

    if (
      // Rock beats Scissors
      ($challenger == RPS_OPTION_ROCK && $defender == RPS_OPTION_SCISSORS) ||
      // Scissors beats Paper
      ($challenger == RPS_OPTION_SCISSORS && $defender == RPS_OPTION_PAPER) ||
      // Paper beats Rock
      ($challenger == RPS_OPTION_PAPER && $defender == RPS_OPTION_ROCK)
    ) {
      return $challenger;
    }
    return $defender;
  }

  public function st_nextRoundRockPaperScissors()
  {
    $options = [
      RPS_OPTION_ROCK => clienttranslate("Rock"),
      RPS_OPTION_PAPER => clienttranslate("Paper"),
      RPS_OPTION_SCISSORS => clienttranslate("Scissors"),
    ];

    $challenger_choice = self::getGameStateValue("rpsChallengerChoice");
    $defender_choice = self::getGameStateValue("rpsDefenderChoice");

    // determine the winner and loser
    $challenger_id = self::getGameStateValue("rpsChallengerId");
    $defender_id = self::getGameStateValue("rpsDefenderId");

    $winner_choice = $this->getWinnerChoice(
      $challenger_choice,
      $defender_choice
    );

    $players = self::loadPlayersBasicInfos();    

    // need distinct player choices, otherwies tie
    if ($winner_choice == null) {
      self::notifyAllPlayers(
        "resultRockPaperScissors",
        clienttranslate('Tie: both players picked ${choice}, try again.'),
        [
          "i18n" => ["choice"],
          "choice" => $options[$challenger_choice],
          "challenger_player_id" => $challenger_id,
          "challenger_player_name" => $players[$challenger_id]["player_name"],
          "challenger_choice_id" => $challenger_choice,          
          "defender_player_id" => $defender_id,
          "defender_player_name" => $players[$defender_id]["player_name"],
          "defender_choice_id" => $defender_choice
        ]
      );

      $this->gamestate->nextstate("continue");
      return;
    }

    $resultMessage = null;
    if ($winner_choice == $challenger_choice) {
      $maxWins = self::incGameStateValue("rpsChallengerWins", 1);
      $winner_id = $challenger_id;
      $loser_id = $defender_id;
      $resultMessage = clienttranslate("beats");
    } else {
      $maxWins = self::incGameStateValue("rpsDefenderWins", 1);
      $winner_id = $defender_id;
      $loser_id = $challenger_id;
      $resultMessage = clienttranslate("is beaten by");
    }

    $players = self::loadPlayersBasicInfos();
    self::notifyAllPlayers(
      "resultRockPaperScissors",
      clienttranslate(
        '${player_name} wins this round: ${challenger_choice} ${result_msg} ${defender_choice}'
      ),
      [
        "i18n" => ["challenger_choice", "result_msg", "defender_choice"],
        "player_name" => $players[$winner_id]["player_name"],
        "challenger_choice" => $options[$challenger_choice],
        "defender_choice" => $options[$defender_choice],
        "result_msg" => $resultMessage,
        "challenger_player_id" => $challenger_id,
        "challenger_player_name" => $players[$challenger_id]["player_name"],
        "challenger_choice_id" => $challenger_choice,          
        "defender_player_id" => $defender_id,
        "defender_player_name" => $players[$defender_id]["player_name"],
        "defender_choice_id" => $defender_choice
      ]
    );

    // default = best of 3-round tournament, so need 2 wins
    // but inflation would make this best of 4, which could lead to 2-2 ties
    // so then need to win 3 rounds
    // https://faq.looneylabs.com/fluxx-games/unthemed-fluxx#q-for-rock-paper-scissors-showdown-do-we-throw-three-times-and-if-its-a-tie-then-nobody-loses-cards
    $addInflation = Utils::getActiveInflation() ? 1 : 0;
    $roundsToWin = 2 + $addInflation;

    // as long as neither has won the best of, keep playing (next round)
    if ($maxWins < $roundsToWin) {
      $this->gamestate->nextstate("continue");
      return;
    }

    // We have a winner! give all hand cards of loser to winner
    self::notifyAllPlayers(
      "winRockPaperScissors",
      clienttranslate('${player_name} wins the Rock-Paper-Scissors showdown'),
      [
        "player_id" => $winner_id,
        "player_name" => $players[$winner_id]["player_name"],
      ]
    );

    // move all cards from loser hand to winner hand
    $game = Utils::getGame();
    $loser_hand = $game->cards->getCardsInLocation("hand", $loser_id);

    $game->notifyPlayer($loser_id, "cardsSentToPlayer", "", [
      "cards" => $loser_hand,
      "player_id" => $winner_id,
    ]);
    $game->notifyPlayer($winner_id, "cardsReceivedFromPlayer", "", [
      "cards" => $loser_hand,
      "player_id" => $loser_id,
    ]);
    $game->cards->moveCards(array_keys($loser_hand), "hand", $winner_id);

    $game->sendHandCountNotifications();

    // done, go back to normal play cards state (after hand limit check for others)
    $this->gamestate->nextstate("handsExchangeOccured");
  }

  public function st_playRockPaperScissors()
  {
    // activate the 2 players that need to battle it out
    $challenger_player_id = self::getGameStateValue("rpsChallengerId");
    $challenged_player_id = self::getGameStateValue("rpsDefenderId");

    $this->gamestate->setPlayersMultiactive(
      [$challenger_player_id, $challenged_player_id],
      "continue",
      true
    );
  }

  public function arg_playRockPaperScissors()
  {
    $challenger_wins = self::getGameStateValue("rpsChallengerWins");
    $defender_wins = self::getGameStateValue("rpsDefenderWins");
    $challenger_id = self::getGameStateValue("rpsChallengerId");
    $defender_id = self::getGameStateValue("rpsDefenderId");

    $players = self::loadPlayersBasicInfos();
    $challenger_name = $players[$challenger_id]["player_name"];
    $defender_name = $players[$defender_id]["player_name"];

    return [
      "challenger_wins" => $challenger_wins,
      "challenger_name" => $challenger_name,
      "defender_wins" => $defender_wins,
      "defender_name" => $defender_name,
      "_private" => [
        $challenger_id => [
          "opponent_name" => $defender_name,
          "my_wins" => $challenger_wins,
          "opponent_wins" => $defender_wins,
        ],
        $defender_id => [
          "opponent_name" => $challenger_name,
          "my_wins" => $defender_wins,
          "opponent_wins" => $challenger_wins,
        ],
      ],
    ];
  }

  /*
   * Player made their choice for the current RockPaperScissors round
   */
  public function action_selectRockPaperScissors($choice)
  {
    self::checkAction("selectRockPaperScissors");
    $player_id = self::getCurrentPlayerId();

    $challenger_id = self::getGameStateValue("rpsChallengerId");
    $defender_id = self::getGameStateValue("rpsDefenderId");

    switch ($choice) {
      case "rock":
        $option = RPS_OPTION_ROCK;
        break;
      case "paper":
        $option = RPS_OPTION_PAPER;
        break;
      case "scissors":
        $option = RPS_OPTION_SCISSORS;
        break;
      default:
        throw new BgaUserException(self::_("This is not a valid choice"));
    }

    // register the choice and wait for other player
    if ($player_id == $challenger_id) {
      self::setGameStateValue("rpsChallengerChoice", $option);
    } elseif ($player_id == $defender_id) {
      self::setGameStateValue("rpsDefenderChoice", $option);
    }

    //@TODO: is it possible to allow the current player to change is mind?
    // why bother? would this be useful?

    // once both players will have made their choice, state will move to check winner state
    $this->gamestate->setPlayerNonMultiactive($player_id, "");
  }
}
