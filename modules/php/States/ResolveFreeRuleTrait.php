<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleCardFactory;

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

  public function arg_resolveFreeRule()
  {
    $card = self::getCurrentResolveFreeRuleCard();
    $freeRuleCard = RuleCardFactory::getCard($card["id"], $card["type_arg"]);

    return [
      "i18n" => ["action_name"],
      "action_id" => $freeRuleCard->getCardId(),
      "action_name" => $freeRuleCard->getName(),
      "action_type" => $freeRuleCard->interactionNeeded,
      "action_args" => $freeRuleCard->resolveArgs(),
      "action_help" => $freeRuleCard->getHelp(),
    ];
  }

  private function _action_resolveFreeRule($args)
  {
    $player_id = self::getActivePlayerId();

    $card = self::getCurrentResolveFreeRuleCard();
    $freeRuleCard = RuleCardFactory::getCard($card["id"], $card["type_arg"]);
    $cardName = $freeRuleCard->getName();

    $stateTransition = $freeRuleCard->resolvedBy($player_id, $args);

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
}
