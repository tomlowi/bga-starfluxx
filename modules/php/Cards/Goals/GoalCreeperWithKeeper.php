<?php
namespace StarFluxx\Cards\Goals;

use StarFluxx\Game\Utils;

class GoalCreeperWithKeeper extends GoalCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->creeper = -1;
    $this->keeper = -1;

    $this->keeperHolograph = 8;
  }

  public function goalReachedByPlayer()
  {
    $winner_id = $this->checkCreeperWithKeeper($this->creeper, $this->keeper);

    return $winner_id;
  }

  private function checkKeeperHasOnlyThisCreeperAttached($keeper_card, $creeper_card)
  {
    $game = Utils::getGame();
    $creeper_def = $game->getCardDefinitionFor($creeper_card);
    // this creeper should be attached to this keeper
    if ($creeper_def->isAttachedTo() != $keeper_card["id"])
      return false;
    // and it should be the only one
    $countCreepers = Utils::countNumberOfCreeperAttached($keeper_card["id"]);
    return ($countCreepers == 1);
  }

  function checkCreeperWithKeeper($creeper, $keeper)
  {
    $game = Utils::getGame();
    $cards = $game->cards;
    $active_player_id = $game->getActivePlayerId();

    $creeper_card = array_values(
      $cards->getCardsOfType("creeper", $creeper)
    )[0];
    $keeper_card = array_values($cards->getCardsOfType("keeper", $keeper))[0];

    // If both cards are not in a player's keepers section, noone wins
    if (
      $creeper_card["location"] != "keepers" or
      $keeper_card["location"] != "keepers"
    ) {
      return null;
    }

    // Exceptionally, the Holographic projection can let player win with Keeper from someone else
    // But only in their turn, so they must be the active player!
    $holograph_player_id = null;
    $player_with_holograph = Utils::findPlayerWithKeeper($this->keeperHolograph);
    if ($player_with_holograph != null) {
      if (!Utils::checkForMalfunction($player_with_holograph["keeper_card"]["id"]))
      {
        $holograph_player_id = $player_with_holograph["player_id"];
      }      
    }
    // https://faq.looneylabs.com/fluxx-games/star-fluxx#1265
    // Active player with holographic projector takes precedence over other player victory!

    // If both cards are in the same player's keepers, this player wins
    $creeper_player_id = $creeper_card["location_arg"];
    $keeper_player_id = $keeper_card["location_arg"];

    if ($holograph_player_id != null && $holograph_player_id == $active_player_id
        && $holograph_player_id != $keeper_player_id) {
      // Holograph player can win by owning the creeper and projecting the keeper
      // But if the creeper and keeper are attached, they can also win because both are holographed together!
      // (providing no other creepers are attached to the same keeper also, preventing the win)
      if ($holograph_player_id == $creeper_player_id
          || $this->checkKeeperHasOnlyThisCreeperAttached($keeper_card, $creeper_card)) {

        $players = $game->loadPlayersBasicInfos();
        $holograph_player_name = $players[$holograph_player_id]["player_name"];

        $card_definition = $game->getCardDefinitionFor($player_with_holograph["keeper_card"]);

        $game->notifyAllPlayers(
          "winWithHolograph",
          clienttranslate(
            '<b>${card_name}</b> allows ${player_name} to win with Keeper from another player'
          ),
          [
            "i18n" => ["card_name"],
            "player_name" => $holograph_player_name,
            "card_name" => $card_definition->getName(),
          ]
        );

        return $holograph_player_id;
      }
    }
    
    if ($creeper_player_id == $keeper_player_id) {
      return $creeper_player_id;
    }

    return null;
  }
}
