<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleCardFactory;
use StarFluxx\Cards\Keepers\KeeperCardFactory;

trait ResolveFreeRuleTrait
{
  function st_resolveFreeRule()
  {
    $player_id = self::getActivePlayerId();
  }

  private function getCurrentResolveFreeRuleCard()
  {
    $game = Utils::getGame();
    $freeRuleCardId = self::getGameStateValue("freeRuleToResolve");
    $card = $game->cards->getCard($freeRuleCardId);
    return $card;
  }

  private function getFreePlayCardDefinition($card) 
  {
    $freePlayCard = null;
    if ($card["type"] == "rule") {
      $freePlayCard = RuleCardFactory::getCard($card["id"], $card["type_arg"]);
    }
    else if ($card["type"] == "keeper") {
      $freePlayCard = KeeperCardFactory::getCard($card["id"], $card["type_arg"]);
    }
    return $freePlayCard;
  }

  public function arg_resolveFreeRule()
  {
    $card = self::getCurrentResolveFreeRuleCard();
    $freePlayCard = $this->getFreePlayCardDefinition($card);

    return [
      "i18n" => ["action_name"],
      "action_id" => $freePlayCard->getCardId(),
      "action_name" => $freePlayCard->getName(),
      "action_type" => $freePlayCard->interactionNeeded,
      "action_args" => $freePlayCard->resolveArgs(),
      "action_help" => $freePlayCard->getHelp(),
    ];
  }

  private function _action_resolveFreeRule($args)
  {
    $player_id = self::getActivePlayerId();

    $card = self::getCurrentResolveFreeRuleCard();
    $freePlayCard = $this->getFreePlayCardDefinition($card);
    
    $cardName = $freePlayCard->getName();

    $stateTransition = $freePlayCard->resolvedBy($player_id, $args);

    self::setGameStateValue("freeRuleToResolve", -1);

    $game = Utils::getGame();

    // If we have a forced move, we cannot win yet
    if ($game->getGameStateValue("forcedCard") == -1) {
      // An action has been resolved: several things might be changed

      // creeper abilities to trigger (need to check this before victory)
      if ($game->checkCreeperResolveNeeded($card)) {
        return;
      }
      //  do we have a new winner?
      $game->checkWinConditions();
      // if not, maybe the card played had effect for any of the bonus conditions?
      $game->checkBonusConditions($player_id);
    }

    if ($stateTransition != null) {
      $game->gamestate->nextstate($stateTransition);
    } else {
      $game->gamestate->nextstate("resolvedFreeRule");
    }
  }

  public function action_resolveFreeRuleCardSelection($card_id)
  {
    self::checkAction("resolveFreeRuleCardSelection");

    $game = Utils::getGame();
    $card = $game->cards->getCard($card_id);

    return self::_action_resolveFreeRule(["card" => $card]);
  }

  public function action_resolveFreeRuleCardsSelection($cards_id)
  {
    self::checkAction("resolveFreeRuleCardsSelection");

    $game = Utils::getGame();

    $cards = [];
    foreach ($cards_id as $card_id) {
      $cards[] = $game->cards->getCard($card_id);
    }
    return self::_action_resolveFreeRule(["cards" => $cards]);
  }

  public function action_resolveFreeRulePlayerSelection($selected_player_id)
  {
    self::checkAction("resolveFreeRulePlayerSelection");
    return self::_action_resolveFreeRule([
      "selected_player_id" => $selected_player_id,
    ]);
  }

  public function action_resolveFreeRuleCardAndPlayerSelection(
    $card_id,
    $selected_player_id
  ) {
    self::checkAction("resolveFreeRuleCardAndPlayerSelection");

    $game = Utils::getGame();
    $card = $game->cards->getCard($card_id);

    return self::_action_resolveFreeRule([
      "card" => $card,
      "selected_player_id" => $selected_player_id,
    ]);
  }
}
