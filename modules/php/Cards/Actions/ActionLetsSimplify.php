<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleCardFactory;
use starfluxx;

class ActionLetsSimplify extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Let’s Simplify");
    $this->description = clienttranslate(
      "Discard your choice of up to half (rounded up) of the New Rule cards in play."
    );

    $this->help =
      clienttranslate(
        "Select any New Rule cards (or none) in play that you want to discard. Click the button when finished."
      );
  }

  public $interactionNeeded = "rulesSelection";

  public function immediateEffectOnPlay($player_id)
  {
    $game = Utils::getGame();
    $rulesInPlay = $game->cards->countCardInLocation("rules");
    if ($rulesInPlay == 0) {
      // no rules on the table, this action does nothing
      return;
    }

    return parent::immediateEffectOnPlay($player_id);
  }

  public function resolveArgs()
  {
    $game = Utils::getGame();
    $rulesInPlay = $game->cards->countCardInLocation("rules");

    return [
      "toDiscardCount" => ceil($rulesInPlay / 2),
    ];
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $cards = $args["cards"];

    $rulesInPlay = $game->cards->countCardInLocation("rules");

    if (count($cards) > ceil($rulesInPlay / 2)) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select up to half (rounded up) of the New Rule cards in play"
        )
      );
    }

    foreach ($cards as $card) {
      if ($card == null || $card["location"] != "rules") {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate(
            "You must select up to half (rounded up) of the New Rule cards in play"
          )
        );
      }

      $ruleCard = RuleCardFactory::getCard($card["id"], $card["type_arg"]);
      $ruleCard->immediateEffectOnDiscard($player_id);

      $game->cards->playCard($card["id"]);
    }
    $game->notifyAllPlayers(
      "rulesDiscarded",
      clienttranslate('${player_name} discarded ${discarded_count} rule(s)'),
      [
        "player_name" => $game->getActivePlayerName(),
        "discarded_count" => count($cards),
        "cards" => $cards,
        "discardCount" => $game->cards->countCardInLocation("discard"),
      ]
    );

    return "rulesChanged";
  }
}
