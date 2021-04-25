<?php
namespace StarFluxx\Cards\Creepers;

use StarFluxx\Game\Utils;
use starfluxx;

class CreeperBrainParasites extends CreeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Brain Parasites");
    $this->subtitle = clienttranslate("Place Immediately + Redraw");
    $this->description = clienttranslate(
      "You cannot win if you have this, unless the Goal says otherwise."
    );

    $this->help = clienttranslate(
      ""
    );
  }

  public function preventsWinForGoal($goalCard)
  {
    $requiredForGoals = [151, 152, 153];
    // Death is required to win with these specific goals:
    // War is Death (151), All That is Certain (152), Death by Chocolate (153)
    if (in_array($goalCard->getUniqueId(), $requiredForGoals)) {
      return false;
    }

    return parent::preventsWinForGoal($goalCard);
  }

  public $interactionNeeded = "keeperSelectionSelf";

  public function onTurnStart()
  {
    $game = Utils::getGame();

    // check who has Death in play now
    $death_player = $this->findPlayerWithDeath();
    // if nobody, nothing to do
    if ($death_player == null) {
      return null;
    }

    $active_player_id = $game->getActivePlayerId();
    $death_player_id = $death_player["player_id"];
    $cardDeath = $death_player["death_card"];
    // Death is not with active player
    if ($active_player_id != $death_player_id) {
      return null;
    }
    // if Death already resolved once on turn start, nothing to do
    // Unless Death is now the only thing remaining for this player!
    $deathCheckCount = $game->getGameStateValue("creeperTurnStartDeathExecuted");
    if ($deathCheckCount == 1) {
      $keepersInPlay = $game->cards->countCardInLocation("keepers", $active_player_id);
      if ($keepersInPlay > 1) {
        return null;
      }
    } else if ($deathCheckCount > 1) {
      return null;
    }
    // Current player has Death and must still resolve it
    $game->setGameStateValue("creeperToResolvePlayerId", $death_player_id);
    $game->setGameStateValue("creeperToResolveCardId", $cardDeath["id"]);

    return parent::onCheckResolveKeepersAndCreepers($cardDeath);
  }

  public function onCheckResolveKeepersAndCreepers($lastPlayedCard)
  {
    // Death can also be discarded any time it stands alone    
    $game = Utils::getGame();
    // don't check Death again after resolving Death itself
    $creeperResolving = $game->getGameStateValue("creeperToResolveCardId");
    if ($lastPlayedCard != null && $lastPlayedCard["id"] == $creeperResolving) {
      return null;
    }

    // Attempt to not have to ask player to discard Death after every single play
    // Probably it is sufficient to ask on turn start, and then only if
    // certain cards have been played like Death itself, goal changes,
    // keepers/creepers moved around

    $interestingCards = [
      53 => "Death",
      // Actions that mess with Keepers
      301 => "",
      314 => "",
      320 => "",
      321 => "",
      // Actions that mess with Creepers
      351 => "",
      352 => "",
      353 => "",
      354 => "",
    ];

    if (
      $lastPlayedCard == null ||
      $lastPlayedCard["type"] == "goal" ||
      array_key_exists($lastPlayedCard["type_arg"], $interestingCards)
    ) {
      return $this->checkResolveDeathAlone();
    }

    return null;
  }

  private function findPlayerWithDeath()
  {
    $game = Utils::getGame();
    // check who has Death in play now
    $death_card = array_values(
      $game->cards->getCardsOfType("creeper", $this->uniqueId)
    )[0];
    // if nobody, nothing to do
    if ($death_card["location"] != "keepers") {
      return null;
    }

    $death_player_id = $death_card["location_arg"];
    return [
      "player_id" => $death_player_id,
      "death_card" => $death_card,
    ];
  }

  private function checkResolveDeathAlone()
  {
    $game = Utils::getGame();
    // check who has Death in play now
    $death_player = $this->findPlayerWithDeath();
    if ($death_player == null) {
      return null;
    }
    $death_player_id = $death_player["player_id"];
    // is it the only thing in play for this player?
    $keepersInPlay = $game->cards->countCardInLocation("keepers", $death_player_id);
    if ($keepersInPlay > 1) {
      return null;
    }    
    // if it is, let this player decide again to keep Death or discard it
    $death_card = $death_player["death_card"];
    $game->setGameStateValue("creeperToResolvePlayerId", $death_player_id);
    $game->setGameStateValue("creeperToResolveCardId", $death_card["id"]);
    return parent::onCheckResolveKeepersAndCreepers($death_card);
  }  

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $card = $args["card"];
    // Death itself can only be removed if no other keepers/creepers in play
    // And only if Death is the only one, nothing needs to be discarded
    $playersKeepersInPlay = $game->cards->countCardInLocation(
      "keepers",
      $player_id
    );

    if ($playersKeepersInPlay > 1) {
      if ($card == null) {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate(
            "You must select a keeper or creeper card you have in play"
          )
        );
      } elseif ($card["type_arg"] == $this->uniqueId) {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate(
            "You cannot discard Death if you have other keeper or creeper cards"
          )
        );
      }
    }

    if ($card == null) {
      // Player has only Death and decided to keep it
      $game->incGameStateValue("creeperTurnStartDeathExecuted", 1);
      return;
    }

    $card_definition = $game->getCardDefinitionFor($card);

    $card_type = $card["type"];
    $card_location = $card["location"];
    $origin_player_id = $card["location_arg"];

    // Death can only kill Keepers or Creepers in play for this player
    if (
      ($card_type != "keeper" && $card_type != "creeper") ||
      $card_location != "keepers" ||
      $origin_player_id != $player_id
    ) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select a keeper or creeper card you have in play"
        )
      );
    }

    $game->incGameStateValue("creeperTurnStartDeathExecuted", 1);
    // move this keeper/creeper to the discard
    $game->cards->playCard($card["id"]);

    $players = $game->loadPlayersBasicInfos();
    $player_name = $players[$origin_player_id]["player_name"];

    $game->notifyAllPlayers(
      "keepersDiscarded",
      clienttranslate('Death killed <b>${card_name}</b> from ${player_name}'),
      [
        "i18n" => ["card_name"],
        "player_name" => $player_name,
        "card_name" => $card_definition->getName(),
        "cards" => [$card],
        "player_id" => $origin_player_id,
        "discardCount" => $game->cards->countCardInLocation("discard"),
        "creeperCount" => Utils::getPlayerCreeperCount($origin_player_id),
      ]
    );
  }
}
