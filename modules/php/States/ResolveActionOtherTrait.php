<?php
namespace StarFluxx\States;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Actions\ActionCardFactory;
use starfluxx;

trait ResolveActionOtherTrait
{
  public function st_actionResolveForOther()
  {
    $otherPlayers = self::loadPlayersBasicInfos();
    // Only other then the active player must resolve this action
    $active_player_id = self::getActivePlayerId();

    if (array_key_exists($active_player_id, $otherPlayers)) {
      unset($otherPlayers[$active_player_id]);
    }

    $gamestate = Utils::getGame()->gamestate;

    // Activate all players that should resolve this action
    $stateTransition = "continuePlay";
    if (empty($otherPlayers)) {
      $gamestate->setAllPlayersNonMultiactive($stateTransition);
    } else {
      $gamestate->setPlayersMultiactive(array_keys($otherPlayers), $stateTransition, true);
    }  
  }

  public function arg_actionResolveForOther()
  {
    $card = self::getCurrentResolveActionCard();
    $actionCard = ActionCardFactory::getCard($card["id"], $card["type_arg"]);

    return [
      "i18n" => ["action_name"],
      "action_id" => $actionCard->getCardId(),
      "action_name" => $actionCard->getName(),
      "action_type" => $actionCard->interactionOther,
      "action_args" => $actionCard->resolveArgs(),
      "action_help" => $actionCard->getHelp(),
    ];
  }

  /*
   * Action resolved with the selected hand card
   */
  function action_resolveActionForOtherByCardSelection($card_id)
  {
    $game = Utils::getGame();

    $player_id = self::getCurrentPlayerId();

    $actionCard = self::getCurrentResolveActionCard();
    $actionCardDef = ActionCardFactory::getCard($actionCard["id"], $actionCard["type_arg"]);
    
    $card = null;
    if ($card_id != null)
      $card = $game->cards->getCard($card_id);

    $actionCardDef->resolvedByOther($player_id, [
      "card" => $card
    ]);

    $stateTransition = "continuePlay";
    $game->gamestate->setPlayerNonMultiactive($player_id, $stateTransition);
  }

}
