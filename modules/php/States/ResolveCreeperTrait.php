<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Creepers\CreeperCardFactory;

trait ResolveCreeperTrait
{
  function st_resolveCreeperInPlay()
  {
    $player_id = self::getGameStateValue("creeperToResolvePlayerId");
    if ($player_id > 0) {
      $gamestate = Utils::getGame()->gamestate;
      $gamestate->setPlayersMultiactive([$player_id], "", true);
      return;
    }
  }

  function st_resolveCreeperTurnStart()
  {
  }

  function st_nextPlayerTurnStartCreepers()
  {
    // Check for any more Creeper abilities on turn start
    $stateTransition = CreeperCardFactory::onTurnStart();
    if ($stateTransition != null) {
      $this->gamestate->nextState($stateTransition);
      return;
    }

    // reset all turn-start creeper execution

    $this->gamestate->nextstate("finishedTurnStartCreepers");
  }

  private function getCurrentResolveCreeperCard()
  {
    $game = Utils::getGame();
    $creeperCardId = self::getGameStateValue("creeperToResolveCardId");
    if ($creeperCardId <= 0) {
      return null;
    }

    $card = $game->cards->getCard($creeperCardId);
    return $card;
  }

  public function arg_resolveCreeper()
  {
    $card = self::getCurrentResolveCreeperCard();
    if ($card == null) {
      return [];
    }

    $creeperCard = CreeperCardFactory::getCard($card["id"], $card["type_arg"]);
    return [
      "i18n" => ["action_name"],
      "action_id" => $creeperCard->getCardId(),
      "action_name" => $creeperCard->getName(),
      "action_type" => $creeperCard->interactionNeeded,
      "action_args" => $creeperCard->resolveArgs(),
      "action_help" => $creeperCard->getHelp(),
    ];
  }

  private function _action_resolveCreeper($args)
  {
    $player_id = self::getCurrentPlayerId();

    $card = self::getCurrentResolveCreeperCard();
    $creeperCard = CreeperCardFactory::getCard($card["id"], $card["type_arg"]);
    $cardName = $creeperCard->getName();

    $stateTransition = $creeperCard->resolvedBy($player_id, $args);

    $game = Utils::getGame();

    // no point in doing creeper/win checks here, gamestate overwritten anyway
    // these checks will be done at start of PlayCardTrait before each play

    // reset these only after checkCreeperResolveNeeded,
    // so they can still be checked to prevent checking the same again
    self::setGameStateValue("creeperToResolveCardId", -1);
    self::setGameStateValue("creeperToResolvePlayerId", -1);

    if ($stateTransition != null) {
      $game->gamestate->nextstate($stateTransition);
    } else {
      $game->gamestate->nextstate("resolvedCreeper");
    }
  }

  public function action_resolveCreeperCardSelection($card_id)
  {
    self::checkAction("resolveCreeperCardSelection");

    $game = Utils::getGame();
    $card = null;
    if ($card_id > 0) {
      $card = $game->cards->getCard($card_id);
    }

    $player_id = self::getCurrentPlayerId();

    return self::_action_resolveCreeper(["card" => $card]);
  }

  public function action_resolveCreeperPlayerSelection($selected_player_id)
  {
    self::checkAction("resolveCreeperPlayerSelection");
    return self::_action_resolveCreeper([
      "selected_player_id" => $selected_player_id,
    ]);
  }

  public function action_resolveCreeperButtons($value)
  {
    self::checkAction("resolveCreeperButtons");
    return self::_action_resolveCreeper(["value" => $value]);
  }
}
