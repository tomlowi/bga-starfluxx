<?php
namespace StarFluxx\Cards\Actions;

use StarFluxx\Game\Utils;
use StarFluxx\Cards\Rules\RuleCardFactory;
use starfluxx;

class ActionVeto extends ActionCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Veto!");
    $this->description = clienttranslate(
      "<b>Out of turn:</b> Discard a New Rule another player just played, thus preventing it from ever taking effect. <b>During your turn:</b> Discard your choice of up to 2 New Rules currently in play.<br>This card can also cancel another Surprise."
    );

    $this->help =
      clienttranslate(
        "Select any New Rule cards (or none) in play that you want to discard. Click the button when finished."
      );
  }

  public function getActionType()
  {
    return "surprise";
  }

  public function outOfTurnCounterPlay($surpriseTargetId)
  {
    $game = Utils::getGame();

    $surpriseCounterId = $this->getCardId();

    $targetCard = $game->cards->getCard($surpriseTargetId);
    $targetPlayerId = $targetCard["location_arg"];
    $surpriseCard = $game->cards->getCard($surpriseCounterId);
    $surpriseCard["location_arg"] = $surpriseCard["location_arg"] % OFFSET_PLAYER_LOCATION_ARG;
    $surprisePlayerId = $surpriseCard["location_arg"];
    $game->cards->playCard($surpriseTargetId);
    $game->cards->playCard($surpriseCounterId);

    // Cancel the Rule played => discard it, and discard this card    
    $discardCount =$game->cards->countCardInLocation("discard");
    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $targetPlayerId,
      "cards" => [$targetCard],
      "discardCount" => $discardCount,
      "handCount" => $game->cards->countCardInLocation("hand", $targetPlayerId),
    ]);
    $game->notifyAllPlayers("handDiscarded", "", [
      "player_id" => $surprisePlayerId,
      "cards" => [$surpriseCard],
      "discardCount" => $discardCount,
      "handCount" => $game->cards->countCardInLocation("hand", $surprisePlayerId),
    ]); 
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
    return [
      "toDiscardCount" => 2,
    ];
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();

    $cards = $args["cards"];

    $rulesInPlay = $game->cards->countCardInLocation("rules");

    if (count($cards) > 2) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate(
          "You must select up to 2 of the New Rule cards in play"
        )
      );
    }

    foreach ($cards as $card) {
      if ($card == null || $card["location"] != "rules") {
        Utils::throwInvalidUserAction(
          starfluxx::totranslate(
            "You must select up to 2 of the New Rule cards in play"
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
