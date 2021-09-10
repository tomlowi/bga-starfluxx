<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * starfluxx implementation : © Iwan Tomlow <iwan.tomlow@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * starfluxx.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */

$swdNamespaceAutoload = function ($class) {
  $classParts = explode("\\", $class);
  if ($classParts[0] == "StarFluxx") {
    array_shift($classParts);
    $file =
      dirname(__FILE__) .
      "/modules/php/" .
      implode(DIRECTORY_SEPARATOR, $classParts) .
      ".php";
    if (file_exists($file)) {
      require_once $file;
    } else {
      var_dump("Impossible to load starfluxx class : $class");
    }
  }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

require_once APP_GAMEMODULE_PATH . "module/table/table.game.php";
require_once "modules/php/constants.inc.php";

use StarFluxx\Cards\Keepers\KeeperCardFactory;
use StarFluxx\Cards\Goals\GoalCardFactory;
use StarFluxx\Cards\Rules\RuleCardFactory;
use StarFluxx\Cards\Actions\ActionCardFactory;
use StarFluxx\Cards\Creepers\CreeperCardFactory;
use StarFluxx\Game\Utils;

class starfluxx extends Table
{
  public static $instance = null;
  public function __construct()
  {
    // Your global variables labels:
    //  Here, you can assign labels to global variables you are using for this game.
    //  You can use any number of global variables with IDs between 10 and 99.
    //  If your game has options (variants), you also have to associate here a label to
    //  the corresponding ID in gameoptions.inc.php.
    // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
    parent::__construct();
    self::$instance = $this;

    self::initGameStateLabels([
      "drawRule" => 10,
      "playRule" => 11,
      "handLimit" => 12,
      "keepersLimit" => 13,
      "drawnCards" => 20,
      "playedCards" => 21,
      "forcedTurnEnd" => 22,
      "playedCardsToIncAfterSurprise" => 23,
      "lastGoalBeforeDoubleAgenda" => 30,
      "activeDoubleAgenda" => 31,
      "activeInflation" => 32,
      "actionToResolve" => 40,
      "anotherTurnMark" => 41,
      "forcedCard" => 42,
      "freeRuleToResolve" => 43,
      "creeperToResolveCardId" => 44,
      "creeperToResolvePlayerId" => 45,
      "creeperBrainParasitesAttachedTo" => 46,
      "creeperEvilAttachedTo" => 47,
      "creeperMalfunctionAttachedTo" => 48,
      "creeperForcedRecheckNeeded" => 49,
      "tmpHand1ToPlay" => 50,
      "tmpHand1Card" => 51,
      "tmpHand2ToPlay" => 52,
      "tmpHand2Card" => 53,
      "tmpHand3ToPlay" => 54,
      "tmpHand3Card" => 55,
      "tmpActionPhase" => 56,
      "cardIdSurpriseTarget" => 57,
      "cardIdSurpriseCounter" => 58,
      "cardIdStolenKeeper" => 59,
      "playerTurnUsedWormhole" => 60,
      "playerTurnUsedCaptain" => 61,
      "playerTurnUsedScientist" => 62,
      "playerTurnUsedLaserPistol" => 63,
      "playerTurnUsedLaserSword" => 64,
      "playerTurnUsedUnseenForce" => 65,
      "playerTurnUsedComputerBonus" => 66,
      "playerTurnLoggedComputerBonus" => 67,
      "playerTurnUsedTeleporter" => 68,
      "playerIdTrapper" => 70,
      "playerIdTrappedTarget" => 71,
    ]);
    $this->cards = self::getNew("module.common.deck");
    $this->cards->init("card");
    // We want to re-shuffle the discard pile in the deck automatically
    $this->cards->autoreshuffle = true;

    $this->cards->autoreshuffle_trigger = [
      "obj" => $this,
      "method" => "deckAutoReshuffle",
    ];
  }

  public static function get()
  {
    return self::$instance;
  }

  // Exposing protected method for translations in modules
  public static function totranslate($text)
  {
    return self::_($text);
  }

  protected function getGameName()
  {
    // Used for translations and stuff. Please do not modify.
    return "starfluxx";
  }

  // for testing purposes only
  public function testForceCardDrawFor($cardType, $cardUniqueId, $player_id) {
    $deckSearch = $this->cards->getCardsOfTypeInLocation($cardType, $cardUniqueId, "deck", null);
    if (count($deckSearch) > 0) {
      $card = array_shift($deckSearch);
      $this->cards->moveCard($card["id"], "hand", $player_id);

      return $card["id"];
    }
  }

  /*
    setupNewGame:

    This method is called only once, when a new game is launched.
    In this method, you must setup the game according to the game rules, so that
    the game is ready to be played.
     */
  protected function setupNewGame($players, $options = [])
  {
    // Set the colors of the players with HTML color code
    // The default below is red/green/blue/orange/brown
    // The number of colors defined here must correspond to the maximum number of players allowed for the gams
    $gameinfos = self::getGameinfos();
    $default_colors = $gameinfos["player_colors"];

    // Create players
    // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
    $sql =
      "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
    $values = [];
    foreach ($players as $player_id => $player) {
      $color = array_shift($default_colors);
      $values[] =
        "('" .
        $player_id .
        "','$color','" .
        $player["player_canal"] .
        "','" .
        addslashes($player["player_name"]) .
        "','" .
        addslashes($player["player_avatar"]) .
        "')";
    }
    $sql .= implode($values, ",");
    self::DbQuery($sql);
    self::reattributeColorsBasedOnPreferences(
      $players,
      $gameinfos["player_colors"]
    );
    self::reloadPlayersBasicInfos();

    /************ Start the game initialization *****/

    $startingDrawRule = 1;
    $startingPlayRule = 1;
    $startingHand = 3;

    // Init global values with their initial values
    self::setGameStateInitialValue("drawRule", $startingDrawRule);
    self::setGameStateInitialValue("playRule", $startingPlayRule);
    self::setGameStateInitialValue("handLimit", -1);
    self::setGameStateInitialValue("keepersLimit", -1);
    self::setGameStateInitialValue("drawnCards", 0);
    self::setGameStateInitialValue("playedCards", 0);
    self::setGameStateInitialValue("playedCardsToIncAfterSurprise", 0);
    self::setGameStateInitialValue("forcedTurnEnd", 0);
    self::setGameStateInitialValue("anotherTurnMark", 0);
    self::setGameStateInitialValue("lastGoalBeforeDoubleAgenda", -1);
    self::setGameStateInitialValue("activeDoubleAgenda", 0);
    self::setGameStateInitialValue("activeInflation", 0);
    self::setGameStateInitialValue("forcedCard", -1);
    self::setGameStateInitialValue("actionToResolve", -1);
    self::setGameStateInitialValue("freeRuleToResolve", -1);
    self::setGameStateInitialValue("creeperToResolveCardId", -1);
    self::setGameStateInitialValue("creeperToResolvePlayerId", -1);

    self::setGameStateInitialValue("creeperBrainParasitesAttachedTo", -1);
    self::setGameStateInitialValue("creeperEvilAttachedTo", -1);
    self::setGameStateInitialValue("creeperMalfunctionAttachedTo", -1);
    self::setGameStateInitialValue("creeperForcedRecheckNeeded", 0);

    self::setGameStateInitialValue("playerTurnUsedWormhole", 0);
    self::setGameStateInitialValue("playerTurnUsedCaptain", 0);
    self::setGameStateInitialValue("playerTurnUsedScientist", 0);
    self::setGameStateInitialValue("playerTurnUsedLaserPistol", 0);
    self::setGameStateInitialValue("playerTurnUsedLaserSword", 0);
    self::setGameStateInitialValue("playerTurnUsedUnseenForce", 0);
    self::setGameStateInitialValue("playerTurnUsedComputerBonus", 0);
    self::setGameStateInitialValue("playerTurnLoggedComputerBonus", 0);
    self::setGameStateInitialValue("playerTurnUsedTeleporter", 0);

    self::setGameStateInitialValue("tmpHand1ToPlay", 0);
    self::setGameStateInitialValue("tmpHand1Card", -1);
    self::setGameStateInitialValue("tmpHand2ToPlay", 0);
    self::setGameStateInitialValue("tmpHand2Card", -1);
    self::setGameStateInitialValue("tmpHand3ToPlay", 0);
    self::setGameStateInitialValue("tmpHand3Card", -1);
    self::setGameStateInitialValue("tmpActionPhase", 0);
    self::setGameStateInitialValue("cardIdSurpriseTarget", -1);
    self::setGameStateInitialValue("cardIdSurpriseCounter", -1);
    self::setGameStateInitialValue("cardIdStolenKeeper", -1);
    self::setGameStateInitialValue("playerIdTrapper", -1);
    self::setGameStateInitialValue("playerIdTrappedTarget", -1);

    // Initialize game statistics
    // (note: statistics used in this file must be defined in your stats.inc.php file)
    //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
    //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)
    self::initStat("table", "turns_number", 0);

    // Create cards
    $cards = [];

    foreach ($this->getAllCardsDefinitions() as $definitionId => $card) {
      // keeper, goal, rule, action

      $cards[] = [
        "type" => $card["type"],
        "type_arg" => $definitionId,
        "nbr" => 1,
      ];
    }

    $this->cards->createCards($cards, "deck");

    // Shuffle deck to start
    $this->cards->shuffle("deck");

    // Each player starts the game with 3 cards

    // Check who should be the first active player
    $this->activeNextPlayer();
    $first_player_id = $this->getActivePlayerId();
    $other_player_id = $first_player_id;
    // If creepers are included, they must already be played when drawn:
    // so we need activated players for that!
    foreach ($players as $player_id => $player) {
      $this->gamestate->changeActivePlayer($player_id);
      $this->performDrawCards($player_id, $startingHand, true);
      $other_player_id = $player_id;
    }

    // $this->testForceCardDrawFor("action", 320, $first_player_id);
    // $this->testForceCardDrawFor("keeper", 12, $first_player_id); 
    // $this->testForceCardDrawFor("creeper", 51, $first_player_id);
    // $this->testForceCardDrawFor("creeper", 52, $other_player_id);
    // $this->testForceCardDrawFor("goal", 110, $other_player_id);
    // $this->testForceCardDrawFor("keeper", 8, $other_player_id);

    // reset to start with correct first active player
    $this->gamestate->changeActivePlayer($first_player_id);
  }

  /*
    getAllDatas:

    Gather all informations about current game situation (visible by the current player).

    The method is called each time the game interface is displayed to a player, ie:
    _ when the game starts
    _ when a player refreshes the game page (F5)
     */
  protected function getAllDatas()
  {
    // We must only return informations visible by this player !!
    $current_player_id = self::getCurrentPlayerId();

    // Get information about players
    $sql = "SELECT player_id id, player_score score FROM player";
    $players = self::getCollectionFromDb($sql);

    $result = [
      "players" => $players,
      "cardTypesDefinitions" => $this->getAllCardTypesDefinitions(),
      "cardsDefinitions" => $this->getAllCardsDefinitions(),
      "hand" => $this->cards->getCardsInLocation("hand", $current_player_id),
      "rules" => [
        "drawRule" => $this->cards->getCardsInLocation("rules", RULE_DRAW_RULE),
        "playRule" => $this->cards->getCardsInLocation("rules", RULE_PLAY_RULE),
        "handLimit" => $this->cards->getCardsInLocation(
          "rules",
          RULE_HAND_LIMIT
        ),
        "keepersLimit" => $this->cards->getCardsInLocation(
          "rules",
          RULE_KEEPERS_LIMIT
        ),
        "others" => $this->cards->getCardsInLocation("rules", RULE_OTHERS),
      ],
      "goals" => $this->cards->getCardsInLocation("goals"),
      "keepers" => [],
      "creepersCount" => [],
      "handsCount" => [],
      "discard" => $this->cards->getCardsInLocation("discard"),
      "deckCount" => $this->cards->countCardInLocation("deck"),
      "discardCount" => $this->cards->countCardInLocation("discard"),
      "creepersAttached" => [
        51 => $this->getGameStateValue("creeperBrainParasitesAttachedTo"),
        52 => $this->getGameStateValue("creeperEvilAttachedTo"),
        53 => $this->getGameStateValue("creeperMalfunctionAttachedTo"),
      ],
      "offsetPlayerLocationArg" => OFFSET_PLAYER_LOCATION_ARG,
    ];

    foreach ($players as $player_id => $player) {
      $result["keepers"][$player_id] = $this->cards->getCardsInLocation(
        "keepers",
        $player_id
      );
      $result["creepersCount"][$player_id] = Utils::getPlayerCreeperCount(
        $player_id
      );
      $result["handsCount"][$player_id] = $this->cards->countCardInLocation(
        "hand",
        $player_id
      );
    }

    return $result;
  }

  /*
    getGameProgression:

    Compute and return the current game progression.
    The number returned must be an integer beween 0 (=the game just started) and
    100 (= the game is finished or almost finished).

    This method is called each time we are in a game state with the "updateGameProgression" property set to true
    (see states.inc.php)
     */
  public function getGameProgression()
  {
    // With starfluxx, there is no way to know when the game ends.
    // This ensures that:
    // - game progression is >50% after the 4th round
    // - it tends to 100% but never goes above it

    $turns = self::getStat("turns_number");
    return round(100 - 100 / exp($turns / 5));
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Utility functions
  ////////////

  /*
    In this space, you can put any utility methods useful for your game logic
   */

  /*
   * Get specific card definition for a card row
   */
  public function getCardDefinitionFor($card)
  {
    $cardType = $card["type"];

    switch ($cardType) {
      case "keeper":
        return KeeperCardFactory::getCard($card["id"], $card["type_arg"]);
      case "goal":
        return GoalCardFactory::getCard($card["id"], $card["type_arg"]);
      case "rule":
        return RuleCardFactory::getCard($card["id"], $card["type_arg"]);
      case "action":
        return ActionCardFactory::getCard($card["id"], $card["type_arg"]);
      case "creeper":
        return CreeperCardFactory::getCard($card["id"], $card["type_arg"]);
      default:
        return null;
    }
  }

  function getAllCardTypesDefinitions()
  {
    return [
      "action" => clienttranslate("Action"),
      "surprise" => clienttranslate("Surprise"),
      "creeper" => clienttranslate("Creeper"),
      "goal" => clienttranslate("Goal"),
      "keeper" => clienttranslate("Keeper"),
      "rule" => clienttranslate("New&nbsp;Rule"),
    ];
  }

  /*
   * Returns all cards definitions using factories
   */

  function getAllCardsDefinitions()
  {
    $keepers = KeeperCardFactory::listCardDefinitions();
    $goals = GoalCardFactory::listCardDefinitions();
    $rules = RuleCardFactory::listCardDefinitions();
    $actions = ActionCardFactory::listCardDefinitions();
    $creepers = CreeperCardFactory::listCardDefinitions();

    return $keepers + $goals + $rules + $actions + $creepers;
  }

  /*
   * Returns player Id based on simultaneous game option state
   */
  function getPlayerIdForAction()
  {
    $state = $this->gamestate->state();
    if ($state["type"] == "multipleactiveplayer") {
      return self::getCurrentPlayerId();
    }
    return self::getActivePlayerId();
  }

  public function getPlayersInOrderForCurrentPlayer()
  {
    $player_id = self::getCurrentPlayerId();
    return $this->getPlayersInOrder($player_id);
  }

  public function getPlayersInOrderForActivePlayer()
  {
    $player_id = self::getActivePlayerId();
    return $this->getPlayersInOrder($player_id);
  }

  /*
   * Return an array of players in natural turn order starting
   * with the current player. This can be used to build the player
   * tables in the same order as the player boards,
   * and for actions that need the players in order.
   */
  private function getPlayersInOrder($startPlayerId)
  {
    $result = [];

    $players = self::loadPlayersBasicInfos();
    $next_player = self::getNextPlayerTable();
    $player_id = $startPlayerId;

    // Check for spectator
    if (!key_exists($player_id, $players)) {
      $player_id = $next_player[0];
    }

    // Build array starting with current player
    for ($i = 0; $i < count($players); $i++) {
      $result[] = $player_id;
      $player_id = $next_player[$player_id];
    }

    return $result;
  }

  private function pickCardsWithCreeperCheck(
    $player_id,
    $drawCount,
    $postponeCreeperResolve
  ) {
    $cardsDrawn = [];
    // If any creepers are drawn, they must be placed immediately,
    // and replaced by a new draw.
    // Since that can trigger a win, they must be drawn 1 by 1
    for ($i = 0; $i < $drawCount; $i++) {
      $nextCard = $this->cards->pickCard("deck", $player_id);
      while ($nextCard != null && $nextCard["type"] == "creeper") {
        // notify so card is shown in hand and can be played
        self::notifyPlayer($player_id, "cardsDrawn", "", [
          "cards" => [$nextCard],
        ]);
        // play card without "checkAction": can be in any state here
        // and don't add this to "play count", and postpone
        self::_action_playCard($nextCard["id"], $player_id, false, $postponeCreeperResolve);
        // re-draw for another card
        $nextCard = $this->cards->pickCard("deck", $player_id);
      }

      if ($nextCard != null) {
        $cardsDrawn[] = $nextCard;
      }
    }

    return $cardsDrawn;
  }

  public function performDrawCards(
    $player_id,
    $drawCount,
    $postponeCreeperResolve = false,
    $temporaryDraw = false
  ) {
    $cardsDrawn = [];
    
    // check for creepers while drawing
    $cardsDrawn = $this->pickCardsWithCreeperCheck(
      $player_id,
      $drawCount,
      $postponeCreeperResolve
    );

    // don't increment drawn counter here, extra cards drawn from actions etc
    // do not count
    if (!$temporaryDraw) {
      self::notifyPlayer($player_id, "cardsDrawn", "", [
        "cards" => $cardsDrawn,
      ]);
    }

    self::notifyAllPlayers(
      "cardsDrawnOther",
      clienttranslate('${player_name} draws <b>${drawCount}</b> card(s)'),
      [
        "player_name" => Utils::getPlayerName($player_id),
        "drawCount" => $drawCount,
        "player_id" => $player_id,
        "handCount" => $this->cards->countCardInLocation("hand", $player_id),
        "deckCount" => $this->cards->countCardInLocation("deck"),
      ]
    );

    // check victory: some goals can also be triggered when extra cards drawn
    if (!$temporaryDraw) {
      $this->checkWinConditions();
    }

    return $cardsDrawn;
  }

  public function sendHandCountNotifications()
  {
    $players = self::loadPlayersBasicInfos();
    $handsCount = [];

    foreach ($players as $player_id => $player) {
      $handsCount[$player_id] = $this->cards->countCardInLocation(
        "hand",
        $player_id
      );
    }

    self::notifyAllPlayers("handCountUpdate", "", [
      "handsCount" => $handsCount,
    ]);
  }

  protected function getLocationArgForRuleType($ruleType)
  {
    switch ($ruleType) {
      case "playRule":
        $location_arg = RULE_PLAY_RULE;
        break;
      case "drawRule":
        $location_arg = RULE_DRAW_RULE;
        break;
      case "keepersLimit":
        $location_arg = RULE_KEEPERS_LIMIT;
        break;
      case "handLimit":
        $location_arg = RULE_HAND_LIMIT;
        break;
      default:
        $location_arg = RULE_OTHERS;
    }

    return $location_arg;
  }

  public function discardRuleCardsForType($ruleType)
  {
    $location_arg = $this->getLocationArgForRuleType($ruleType);

    // We discard the conflicting rule cards
    $cards = $this->cards->getCardsInLocation("rules", $location_arg);
    $player_id = self::getCurrentPlayerId();

    foreach ($cards as $card_id => $card) {
      $rule = RuleCardFactory::getCard($card_id, $card["type_arg"]);
      $rule->immediateEffectOnDiscard($player_id);
      $this->cards->playCard($card_id);
    }

    if ($cards) {
      self::notifyAllPlayers("rulesDiscarded", "", [
        "cards" => $cards,
        "discardCount" => $this->cards->countCardInLocation("discard"),
      ]);
    }
  }

  public function discardCardsFromLocation(
    $cards_id,
    $location,
    $location_arg,
    $expected_type
  ) {
    $cards = [];
    foreach ($cards_id as $card_id) {
      // Verify card is in the right location
      $card = $this->cards->getCard($card_id);
      if (
        $card == null ||
        $card["location"] != $location ||
        $card["location_arg"] != $location_arg
      ) {
        throw new BgaUserException(
          self::_("Impossible discard: invalid card ") . $card_id
        );
      }
      // and of the expected type to be discarded (if specified)
      if ($expected_type != null && $expected_type != $card["type"]) {
        throw new BgaUserException(
          self::_("Illegal discard: card must be of type ") . $expected_type
        );
      }

      $cards[$card["id"]] = $card;

      // Discard card
      $this->cards->playCard($card["id"]);
    }
    return $cards;
  }

  public function deckAutoReshuffle()
  {
    self::notifyAllPlayers("reshuffle", "", [
      "deckCount" => $this->cards->countCardInLocation("deck"),
      "discardCount" => $this->cards->countCardInLocation("discard"),
    ]);
  }

  public function checkBonusConditions($player_id)
  {
    // check for any draw bonuses again after cards played
  }

  public function checkCreeperResolveNeeded($lastPlayedCard)
  {
    self::setGameStateValue("creeperForcedRecheckNeeded", 0);
    // Check for any Creeper abilities after keepers/creepers played or moved
    $stateTransition = CreeperCardFactory::onCheckResolveKeepersAndCreepers(
      $lastPlayedCard
    );
    if ($stateTransition != null) {
      $this->gamestate->nextState($stateTransition);
    }
    return $stateTransition != null;
  }

  public function checkWinConditions()
  {
    $winnerInfo = $this->checkCurrentGoalsWinner();
    if ($winnerInfo == null) {
      return false;
    }

    // We have one winner, no tie
    $winnerId = $winnerInfo["winner"];

    // set final score
    $sql = "UPDATE player SET player_score=1  WHERE player_id='$winnerId'";
    self::DbQuery($sql);

    $newScores = self::getCollectionFromDb(
      "SELECT player_id, player_score FROM player",
      true
    );
    self::notifyAllPlayers("newScores", "", [
      "newScores" => $newScores,
    ]);

    $players = self::loadPlayersBasicInfos();
    self::notifyAllPlayers(
      "win",
      clienttranslate('${player_name} wins with goal <b>${goal_name}</b>'),
      [
        "i18n" => ["goal_name"],
        "player_id" => $winnerId,
        "player_name" => $players[$winnerId]["player_name"],
        "goal_id" => $winnerInfo["goalId"],
        "goal_name" => $winnerInfo["goal"],
      ]
    );

    $this->gamestate->nextState("endGame");
    return true;
  }

  public function checkCurrentGoalsWinner()
  {
    $winnerId = null;
    $winningGoalCard = null;
    $goals = $this->cards->getCardsInLocation("goals");

    // if more than 2 goals in play (Double Agenda), we have to wait 
    // until choice has been made which goal to discard (this cannot win)
    if (count($goals) > 2) {
      return null;
    }

    foreach ($goals as $card_id => $card) {
      $goalCard = GoalCardFactory::getCard($card["id"], $card["type_arg"]);

      $goalReachedByPlayerId = $goalCard->goalReachedByPlayer();      
      if (
        $goalReachedByPlayerId != null &&
        $goalCard->isWinPreventedByCreepers($goalReachedByPlayerId, $goalCard)
      ) {
        // notify that player could have won but was prevented by creeper
        $players = self::loadPlayersBasicInfos();
        $unlucky_player_name = $players[$goalReachedByPlayerId]["player_name"];
        self::notifyAllPlayers(
          "winPreventedByCreeper",
          clienttranslate(
            'Creepers prevent ${player_name2} from winning with <b>${goal_name}</b>'
          ),
          [
            "i18n" => ["goal_name"],
            "goal_name" => $goalCard->getName(),
            "player_name2" => $unlucky_player_name,
          ]
        );
        // sorry, but you can't win yet
        $goalReachedByPlayerId = null;
      }

      if ($goalReachedByPlayerId != null) {
        // some player reached this goal
        if ($winnerId != null && $goalReachedByPlayerId != $winnerId) {
          // if multiple goals reached by different players, keep playing
          return null;
        }
        // this player is the winner, unless someone else also reached another goal
        $winnerId = $goalReachedByPlayerId;
        $winningGoalCard = $goalCard;
      }
    }

    if ($winnerId == null) {
      return null;
    }

    return [
      "winner" => $winnerId,
      "goal" => $winningGoalCard->getName(),
      "goalId" => $winningGoalCard->getCardId(),
    ];
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Player actions
  ////////////

  /*
    Each time a player is doing some game action, one of the methods below is called.
    (note: each method below must match an input method in starfluxx.action.php)
     */

  /*
   * Player discards a goal after double agenda
   */
  public function action_discardGoal($card_id)
  {
    self::checkAction("discardGoal");
    $player_id = self::getActivePlayerId();
    $card = $this->cards->getCard($card_id);

    $lastPlayedGoal = self::getGameStateValue("lastGoalBeforeDoubleAgenda");

    if ($card["id"] == $lastPlayedGoal) {
      throw new BgaUserException(
        self::_("You cannot discard the goal card you just played.")
      );
    }

    if ($card["location"] != "goals") {
      throw new BgaUserException(self::_("This goal is not in play."));
    }

    // Discard card
    $this->cards->playCard($card["id"]);

    self::notifyAllPlayers("goalsDiscarded", "", [
      "cards" => [$card],
      "discardCount" => $this->cards->countCardInLocation("discard"),
    ]);

    self::setGameStateValue("lastGoalBeforeDoubleAgenda", -1);
    $this->gamestate->nextstate("continuePlay");

    // check win *after* decision which goals to keep
    $this->checkWinConditions();
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Game state arguments
  ////////////

  /*
    Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
    These methods function is to return some additional information that is specific to the current
    game state.
     */

  use StarFluxx\States\DrawCardsTrait;
  use StarFluxx\States\PlayCardTrait;
  use StarFluxx\States\HandLimitTrait;
  use StarFluxx\States\KeepersLimitTrait;
  use StarFluxx\States\ResolveActionTrait;
  use StarFluxx\States\ResolveFreeRuleTrait;
  use StarFluxx\States\ResolveCreeperTrait;
  use StarFluxx\States\TempHandPlayTrait;
  use StarFluxx\States\SurpriseCounterPlayTrait;
  use StarFluxx\States\SurpriseCancelPlayTrait;
  use StarFluxx\States\ResolveActionOtherTrait;

  //////////////////////////////////////////////////////////////////////////////
  //////////// Game state actions
  ////////////

  public function st_goalCleaning()
  {
    $hasDoubleAgenda = Utils::getActiveDoubleAgenda();
    $existingGoalCount = $this->cards->countCardInLocation("goals");

    $expectedCount = $hasDoubleAgenda ? 2 : 1;

    if ($existingGoalCount <= $expectedCount) {
      // We already have the proper number of goals, proceed to play
      $this->gamestate->nextstate("continuePlay");
      return;
    }

    self::setGameStateValue("creeperForcedRecheckNeeded", 1);
  }

  public function st_nextPlayer()
  {
    // special case: current player received another turn
    $anotherTurnMark = self::getGameStateValue("anotherTurnMark");
    $player_id = -1;
    $active_player = self::getActivePlayerId();

    // Some Keeper abilities activate on turn end
    KeeperCardFactory::onTurnEnd();

    if ($anotherTurnMark == 1) {
      // Take Another Turn can only be used once (two turns in a row)
      self::setGameStateValue("anotherTurnMark", 2);
      $player_id = self::getActivePlayerId();
      self::notifyAllPlayers(
        "turnFinished",
        clienttranslate('${player_name} can take another turn!'),
        [
          "player_id" => $active_player,
          "player_name" => self::getCurrentPlayerName(),
        ]
      );
    } else {
      self::setGameStateValue("anotherTurnMark", 0);
      self::notifyAllPlayers(
        "turnFinished",
        clienttranslate('${player_name} finished their turn.'),
        [
          "player_id" => $active_player,
          "player_name" => self::getActivePlayerName(),
        ]
      );
      $player_id = self::activeNextPlayer();      

      $players = self::loadPlayersBasicInfos();
      reset($players);
      $first_player = key($players);
      if ($first_player == $active_player) {
        // Full Turns played during the game
        self::incStat(1, "turns_number");
      }
    }

    // reset everything for turn of next player
    self::setGameStateValue("playedCards", 0);
    self::setGameStateValue("playedCardsToIncAfterSurprise", 0);
    self::setGameStateValue("forcedTurnEnd", 0);
    self::setGameStateValue("playerTurnUsedWormhole", 0);
    self::setGameStateValue("playerTurnUsedCaptain", 0);
    self::setGameStateValue("playerTurnUsedScientist", 0);
    self::setGameStateValue("playerTurnUsedLaserPistol", 0);
    self::setGameStateValue("playerTurnUsedLaserSword", 0);
    self::setGameStateValue("playerTurnUsedUnseenForce", 0);
    self::setGameStateValue("playerTurnUsedComputerBonus", 0);
    self::setGameStateValue("playerTurnLoggedComputerBonus", 0);
    self::setGameStateValue("playerTurnUsedTeleporter", 0);
    // also reset all turn-start creeper execution
    self::setGameStateValue("creeperForcedRecheckNeeded", 0);

    self::giveExtraTime($player_id);
    $this->gamestate->nextState("");
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// Zombie
  ////////////

  /*
    zombieTurn:

    This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
    You can do whatever you want in order to make sure the turn of this player ends appropriately
    (ex: pass).

    Important: your zombie code will be called when the player leaves the game. This action is triggered
    from the main site and propagated to the gameserver from a server, not from a browser.
    As a consequence, there is no current player associated to this action. In your zombieTurn function,
    you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
     */

  public function zombieTurn($state, $active_player)
  {
    $statename = $state["name"];

    if ($state["type"] === "activeplayer") {
      switch ($statename) {
        default:
        // zombie player: discards any remaining cards in hand
        // then just zombie pass
          $cards = $this->cards->getCardsInLocation("hand", $active_player);
          // discard all cards
          foreach ($cards as $card_id => $card) {
            $this->cards->playCard($card_id);
          }

          $this->notifyAllPlayers("handDiscarded", "", [
            "player_id" => $active_player,
            "cards" => $cards,
            "discardCount" => $this->cards->countCardInLocation("discard"),
            "handCount" => $this->cards->countCardInLocation("hand", $active_player),
          ]);

          $this->gamestate->nextState("zombiePass");
          break;
      }

      return;
    }

    if ($state["type"] === "multipleactiveplayer") {
      // Make sure player is in a non blocking status for role turn
      $this->gamestate->setPlayerNonMultiactive($active_player, "");

      return;
    }

    throw new feException(
      "Zombie mode not supported at this game state: " . $statename // NOI18N
    );
  }

  ///////////////////////////////////////////////////////////////////////////////////:
  ////////// DB upgrade
  //////////

  /*
    upgradeTableDb:

    You don't have to care about this until your game has been published on BGA.
    Once your game is on BGA, this method is called everytime the system detects a game running with your old
    Database scheme.
    In this case, if you change your Database scheme, you just have to apply the needed changes in order to
    update the game database and allow the game to continue to run with your new version.

     */

  public function upgradeTableDb($from_version)
  {
    // $from_version is the current version of this game database, in numerical form.
    // For example, if the game was running with a release of your game named "140430-1345",
    // $from_version is equal to 1404301345

    // Example:
    //        if( $from_version <= 1404301345 )
    //        {
    //            // ! important ! Use DBPREFIX_<table_name> for all tables
    //
    //            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
    //            self::applyDbUpgradeToAllDB( $sql );
    //        }
    //        if( $from_version <= 1405061421 )
    //        {
    //            // ! important ! Use DBPREFIX_<table_name> for all tables
    //
    //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
    //            self::applyDbUpgradeToAllDB( $sql );
    //        }
    //        // Please add your future database scheme changes here
    //
    //
  }
}
