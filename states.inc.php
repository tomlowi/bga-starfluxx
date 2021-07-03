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
 * states.inc.php
 *
 * starfluxx game states description
 *
 */

/*
Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
in a very easy way from this configuration file.

Please check the BGA Studio presentation about game state to understand this, and associated documentation.
 
Summary:

States types:
_ activeplayer: in this type of state, we expect some action from the active player.
_ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
_ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
_ manager: special type for initial and final state

Arguments of game states:
_ name: the name of the GameState, in order you can recognize it on your own code.
_ description: the description of the current game state is always displayed in the action status bar on
the top of the game. Most of the time this is useless for game state with "game" type.
_ descriptionmyturn: the description of the current game state when it's your turn.
_ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
_ action: name of the method to call when this game state become the current game state. Usually, the
action method is prefixed by "st" (ex: "stMyGameStateName").
_ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
_ transitions: the transitions are the possible paths to go from a game state to another. You must name
transitions in order to use transition names in "nextState" PHP method, and use IDs to
specify the next game state for each transition.
_ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
client side to be used on "onEnteringState" or to set arguments in the gamestate description.
_ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
method).
 */

//    !! It is not a good idea to modify this file when a game is running !!

// Define Constants
if (!defined("STATE_GAME_SETUP")) {
  define("STATE_GAME_SETUP", 1);
  define("STATE_GAME_END", 99);
  define("STATE_DRAW_CARDS", 10);
  define("STATE_PLAY_CARD", 20);
  define("STATE_ENFORCE_HAND_LIMIT_OTHERS", 21);
  define("STATE_ENFORCE_KEEPERS_LIMIT_OTHERS", 22);
  define("STATE_ENFORCE_HAND_LIMIT_SELF", 23);
  define("STATE_ENFORCE_KEEPERS_LIMIT_SELF", 24);
  define("STATE_GOAL_CLEANING", 25);
  define("STATE_RESOLVE_ACTION", 30);
  define("STATE_RESOLVE_FREE_RULE", 33);
  define("STATE_RESOLVE_CREEPER_TURNSTART", 34);
  define("STATE_RESOLVE_CREEPER_INPLAY", 35);
  define("STATE_RESOLVE_TEMP_HAND_PLAY", 36);
  define("STATE_ALLOW_SURPRISE_COUNTER_PLAY", 37);
  define("STATE_ALLOW_SURPRISE_CANCEL_SURPRISE", 38);
  define("STATE_NEXT_PLAYER_TURNSTART_CREEPERS", 89);
  define("STATE_NEXT_PLAYER", 90);
}

$machinestates = [
  // The initial state. Please do not modify.
  STATE_GAME_SETUP => [
    "name" => "gameSetup",
    "description" => "",
    "type" => "manager",
    "action" => "stGameSetup",
    "transitions" => ["" => STATE_DRAW_CARDS],
  ],

  STATE_DRAW_CARDS => [
    "name" => "drawCards",
    "description" => "",
    "type" => "game",
    "action" => "st_drawCards",
    "transitions" => [
      "cardsDrawn" => STATE_PLAY_CARD,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_PLAY_CARD => [
    "name" => "playCard",
    "description" => clienttranslate(
      '${actplayer} must play ${countLabelText}${countLabelNr} card(s)'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must play ${countLabelText}${countLabelNr} card(s)'
    ),
    "type" => "activeplayer",
    "action" => "st_playCard",
    "args" => "arg_playCard",
    "possibleactions" => ["playCard", "playFreeRule", "finishTurn"],
    "transitions" => [
      "handLimitRulePlayed" => STATE_ENFORCE_HAND_LIMIT_OTHERS,
      "keepersLimitRulePlayed" => STATE_ENFORCE_KEEPERS_LIMIT_OTHERS,
      "keepersExchangeOccured" => STATE_ENFORCE_KEEPERS_LIMIT_OTHERS,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
      "doubleAgendaRule" => STATE_GOAL_CLEANING,
      "rulesChanged" => STATE_GOAL_CLEANING,

      "resolveActionCard" => STATE_RESOLVE_ACTION,
      "resolveFreeRule" => STATE_RESOLVE_FREE_RULE,
      "resolveCreeper" => STATE_RESOLVE_CREEPER_INPLAY,
      "resolveTempHand" => STATE_RESOLVE_TEMP_HAND_PLAY,
      "continuePlay" => STATE_PLAY_CARD,
      "endGame" => STATE_GAME_END,

      "zombiePass" => STATE_ENFORCE_HAND_LIMIT_SELF,

      "checkForSurprises" => STATE_ALLOW_SURPRISE_COUNTER_PLAY,
    ],
  ],

  STATE_ENFORCE_HAND_LIMIT_OTHERS => [
    "name" => "enforceHandLimitForOthers",
    "description" => clienttranslate(
      'Some players must discard card(s) for Hand Limit ${limit}${warnInflation}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} can only keep ${_private.actualLimit} card(s) (discard ${_private.discardCount}) for Hand Limit ${limit}${warnInflation}'
    ),
    "type" => "multipleactiveplayer",
    "args" => "arg_enforceHandLimitForOthers",
    "action" => "st_enforceHandLimitForOthers",
    "possibleactions" => ["discardHandCardsExcept"],
    "transitions" => [
      "handLimitChecked" => STATE_ENFORCE_KEEPERS_LIMIT_OTHERS,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_ENFORCE_KEEPERS_LIMIT_OTHERS => [
    "name" => "enforceKeepersLimitForOthers",
    "description" => clienttranslate(
      'Some players must remove keeper(s) for Keeper Limit ${limit}${warnInflation}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must remove ${_private.discardCount} keeper(s) for Keeper Limit ${limit}${warnInflation}'
    ),
    "type" => "multipleactiveplayer",
    "args" => "arg_enforceKeepersLimitForOthers",
    "action" => "st_enforceKeepersLimitForOthers",
    "possibleactions" => ["discardKeepers"],
    "transitions" => [
      "keeperLimitChecked" => STATE_PLAY_CARD,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_ENFORCE_HAND_LIMIT_SELF => [
    "name" => "enforceHandLimitForSelf",
    "description" => clienttranslate(
      '${actplayer} must discard card(s) for Hand Limit ${limit}${warnInflation}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} can only keep ${_private.actualLimit} card(s) (discard ${_private.discardCount}) for Hand Limit ${limit}${warnInflation}'
    ),
    "type" => "activeplayer",
    "args" => "arg_enforceHandLimitForSelf",
    "action" => "st_enforceHandLimitForSelf",
    "possibleactions" => ["discardHandCardsExcept"],
    "transitions" => [
      "handLimitChecked" => STATE_ENFORCE_KEEPERS_LIMIT_SELF,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_ENFORCE_KEEPERS_LIMIT_SELF => [
    "name" => "enforceKeepersLimitForSelf",
    "description" => clienttranslate(
      '${actplayer} must remove keeper(s) for Keeper Limit ${limit}${warnInflation}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must remove ${_private.discardCount} keeper(s) for Keeper Limit ${limit}${warnInflation}'
    ),
    "type" => "activeplayer",
    "args" => "arg_enforceKeepersLimitForSelf",
    "action" => "st_enforceKeepersLimitForSelf",
    "possibleactions" => ["discardKeepers"],
    "transitions" => [
      "keeperLimitChecked" => STATE_NEXT_PLAYER,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_GOAL_CLEANING => [
    "name" => "goalCleaning",
    "description" => clienttranslate('${actplayer} must discard a goal'),
    "descriptionmyturn" => clienttranslate('${you} must discard a goal'),
    "type" => "activeplayer",
    "action" => "st_goalCleaning",
    "possibleactions" => ["discardGoal"],
    "transitions" => [
      "continuePlay" => STATE_PLAY_CARD,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_RESOLVE_ACTION => [
    "name" => "actionResolve",
    "description" => clienttranslate(
      '${actplayer} must resolve their action: <i>${action_name}</i>'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must resolve your action: <i>${action_name}</i>'
    ),
    "type" => "activeplayer",
    "args" => "arg_resolveAction",
    //"action" => "st_resolveAction",
    "possibleactions" => [
      "resolveAction",
      "resolveActionPlayerSelection",
      "resolveActionCardAndPlayerSelection",
      "resolveActionCardSelection",
      "resolveActionCardsSelection",
      "resolveActionKeepersExchange",
      "resolveActionButtons",
    ],
    "transitions" => [
      "resolvedAction" => STATE_PLAY_CARD,
      "resolveCreeper" => STATE_RESOLVE_CREEPER_INPLAY,
      "handsExchangeOccured" => STATE_ENFORCE_HAND_LIMIT_OTHERS,
      "keepersExchangeOccured" => STATE_ENFORCE_KEEPERS_LIMIT_OTHERS,
      "rulesChanged" => STATE_GOAL_CLEANING,
      "endGame" => STATE_GAME_END,
      "resolveActionCard" => STATE_RESOLVE_ACTION,
      "zombiePass" => STATE_PLAY_CARD,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
    ],
  ],

  STATE_RESOLVE_FREE_RULE => [
    "name" => "freeRuleResolve",
    "description" => clienttranslate(
      '${actplayer} must resolve their free play: <i>${action_name}</i>'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must resolve your free play: <i>${action_name}</i>'
    ),
    "type" => "activeplayer",
    "args" => "arg_resolveFreeRule",
    //"action" => "st_resolveFreeRule",
    "possibleactions" => [
      "resolveFreeRule",
      "resolveFreeRuleCardSelection",
      "resolveFreeRuleCardsSelection",
      "resolveFreeRuleButtons",
      "resolveFreeRulePlayerSelection",
      "resolveFreeRuleCardAndPlayerSelection",
    ],
    "transitions" => [
      "resolvedFreeRule" => STATE_PLAY_CARD,
      "resolveCreeper" => STATE_RESOLVE_CREEPER_INPLAY,
      "endGame" => STATE_GAME_END,
      "zombiePass" => STATE_PLAY_CARD,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
    ],
  ],

  STATE_RESOLVE_CREEPER_INPLAY => [
    "name" => "creeperResolveInPlay",
    "description" => clienttranslate(
      '${actplayer} must resolve Creeper: <i>${action_name}</i>'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must resolve Creeper: <i>${action_name}</i>'
    ),
    "type" => "multipleactiveplayer",
    "args" => "arg_resolveCreeper",
    "action" => "st_resolveCreeperInPlay",
    "possibleactions" => [
      "resolveCreeperCardSelection",
      "resolveCreeperPlayerSelection",
      "resolveCreeperButtons",
    ],
    "transitions" => [
      "resolveCreeper" => STATE_RESOLVE_CREEPER_INPLAY,
      "resolvedCreeper" => STATE_PLAY_CARD,
      "resolvedFreeRule" => STATE_PLAY_CARD,
      "resolvedAction" => STATE_PLAY_CARD,
      "continuePlay" => STATE_PLAY_CARD,
      "endGame" => STATE_GAME_END,
      "zombiePass" => STATE_PLAY_CARD,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
    ],
  ],

  STATE_RESOLVE_CREEPER_TURNSTART => [
    "name" => "creeperResolveTurnStart",
    "description" => clienttranslate(
      '${actplayer} must resolve Creeper: <i>${action_name}</i>'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must resolve Creeper: <i>${action_name}</i>'
    ),
    "type" => "activeplayer",
    "args" => "arg_resolveCreeper",
    "action" => "st_resolveCreeperTurnStart",
    "possibleactions" => [
      "resolveCreeperCardSelection",
      "resolveCreeperPlayerSelection",
      "resolveCreeperButtons",
    ],
    "transitions" => [
      "resolvedCreeper" => STATE_NEXT_PLAYER_TURNSTART_CREEPERS,
      "zombiePass" => STATE_NEXT_PLAYER_TURNSTART_CREEPERS,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_RESOLVE_TEMP_HAND_PLAY => [
    "name" => "tempHandPlay",
    "description" => clienttranslate(
      '${actplayer} must play ${tmpToPlay} card(s) from ${tmpHandName}'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} must play ${tmpToPlay} card(s) from ${tmpHandName}'
    ),
    "type" => "activeplayer",
    "action" => "st_tempHandPlay",
    "args" => "arg_tempHandPlay",
    "possibleactions" => ["selectTempHandCard"],
    "transitions" => [
      "selectedCard" => STATE_PLAY_CARD,
      "zombiePass" => STATE_PLAY_CARD,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_NEXT_PLAYER_TURNSTART_CREEPERS => [
    "name" => "nextPlayerTurnStartCreepers",
    "description" => "",
    "type" => "game",
    "action" => "st_nextPlayerTurnStartCreepers",
    "updateGameProgression" => false,
    "transitions" => [
      "resolveCreeper" => STATE_RESOLVE_CREEPER_TURNSTART,
      "finishedTurnStartCreepers" => STATE_DRAW_CARDS,
      "zombiePass" => STATE_DRAW_CARDS,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_NEXT_PLAYER => [
    "name" => "nextPlayer",
    "description" => "",
    "type" => "game",
    "action" => "st_nextPlayer",
    "updateGameProgression" => true,
    "transitions" => [
      "" => STATE_NEXT_PLAYER_TURNSTART_CREEPERS,
    ],
  ],

  STATE_ALLOW_SURPRISE_COUNTER_PLAY => [
    "name" => "surpriseCounterPlay",
    "description" => clienttranslate(
      'Some players may choose to Surprise counter <i>${playedCardName}</i>'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} can choose to Surprise counter <i>${playedCardName}</i>'
    ),
    "type" => "multipleactiveplayer",
    "args" => "arg_allowSurpriseCounterPlay",
    "action" => "st_allowSurpriseCounterPlay",
    "possibleactions" => [
      "decideSurpriseCounterPlay"
    ],
    "transitions" => [
      //"surprisePlayChecked" => STATE_ALLOW_SURPRISE_CANCEL_SURPRISE,
      "surprisePlayChecked" => STATE_PLAY_CARD,
      "zombiePass" => STATE_PLAY_CARD,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
      "endGame" => STATE_GAME_END,
    ],
  ],

  STATE_ALLOW_SURPRISE_CANCEL_SURPRISE => [
    "name" => "surpriseCancelSurprise",
    "description" => clienttranslate(
      'Some players may choose to cancel the Surprise <i>${surpriseName}</i>'
    ),
    "descriptionmyturn" => clienttranslate(
      '${you} can choose to cancel the Surprise <i>${surpriseName}</i>'
    ),
    "type" => "multipleactiveplayer",
    "args" => "arg_allowSurpriseCancelSurprise",
    "action" => "st_allowSurpriseCancelSurprise",
    "possibleactions" => [
      "resolveSurprisePlay",
      "resolveSurpriseIgnore"
    ],
    "transitions" => [
      //"surprisePlayChecked" => STATE_ALLOW_SURPRISE_CANCEL_SURPRISE,
      "surprisePlayChecked" => STATE_PLAY_CARD,
      "zombiePass" => STATE_PLAY_CARD,
      "endOfTurn" => STATE_ENFORCE_HAND_LIMIT_SELF,
      "endGame" => STATE_GAME_END,
    ],
  ],

  /*
Examples:

2 => array(
"name" => "nextPlayer",
"description" => '',
"type" => "game",
"action" => "stNextPlayer",
"updateGameProgression" => true,
"transitions" => array( "endGame" => 99, "nextPlayer" => 10 )
),

10 => array(
"name" => "playerTurn",
"description" => clienttranslate('${actplayer} must play a card or pass'),
"descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
"type" => "activeplayer",
"possibleactions" => array( "playCard", "pass" ),
"transitions" => array( "playCard" => 2, "pass" => 2 )
),

 */

  // Final state.
  // Please do not modify (and do not overload action/args methods).
  STATE_GAME_END => [
    "name" => "gameEnd",
    "description" => clienttranslate("End of game"),
    "type" => "manager",
    "action" => "stGameEnd",
    "args" => "argGameEnd",
  ],
];
