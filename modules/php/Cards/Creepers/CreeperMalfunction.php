<?php
namespace StarFluxx\Cards\Creepers;

use StarFluxx\Game\Utils;
use starfluxx;

class CreeperMalfunction extends CreeperCard
{
  public function __construct($cardId, $uniqueId)
  {
    parent::__construct($cardId, $uniqueId);

    $this->name = clienttranslate("Malfunction");
    $this->subtitle = clienttranslate("Place Immediately + Redraw");
    $this->description = clienttranslate(
      "You cannot win if you have this, unless the Goal says otherwise."
    );

    $this->help = clienttranslate(
      "Decide if you want to pay Taxes with Money now, or if you still want to keep your Money."
    );
  }

  public function preventsWinForGoal($goalCard)
  {
    $requiredForGoals = [152, 156];
    // Taxes is required to win with these specific goals:
    // All That is Certain (152), Your Tax Dollars at War (156)
    if (in_array($goalCard->getUniqueId(), $requiredForGoals)) {
      return false;
    }

    return parent::preventsWinForGoal($goalCard);
  }

  // Rules clarification from Andy:
  // Paying your Taxes is optional and can be done at any time...
  // So we need to keep asking player each time anything changed.
  // Ask at turn start, and whenever some relevant action/rule/keeper/creeper card was played
  public $interactionNeeded = "buttons";

  public function resolveArgs()
  {
    return [
      ["value" => "pay", "label" => clienttranslate("Pay Taxes")],
      ["value" => "keep", "label" => clienttranslate("Keep my Money")],
    ];
  }

  private function findPlayerWithTaxesAndMoney()
  {
    $game = Utils::getGame();
    // check who has Taxes in play now
    $cardTaxes = array_values(
      $game->cards->getCardsOfType("creeper", $this->uniqueId)
    )[0];
    // if nobody, nothing to do
    if ($cardTaxes["location"] != "keepers") {
      return null;
    }

    $taxes_player_id = $cardTaxes["location_arg"];
    // If same player has Money
    // => Money can be used to pay Taxes and both are discarded

    $money_unique_id = 7;
    $cardMoney = array_values(
      $game->cards->getCardsOfType("keeper", $money_unique_id)
    )[0];

    if (
      $cardMoney["location"] == "keepers" &&
      $cardMoney["location_arg"] == $taxes_player_id
    ) {
      return [
        "player_id" => $taxes_player_id,
        "tax_card" => $cardTaxes,
        "money_card" => $cardMoney,
      ];
    }

    return null;
  }

  private function checkResolveTaxesAndMoney($onlyForActivePlayer)
  {
    $taxesAndMoney = $this->findPlayerWithTaxesAndMoney();
    if ($taxesAndMoney == null) {
      return null;
    }

    $game = Utils::getGame();
    if (
      $onlyForActivePlayer &&
      $game->getActivePlayerId() != $taxesAndMoney["player_id"]
    ) {
      return null;
    }

    $game->setGameStateValue(
      "creeperToResolvePlayerId",
      $taxesAndMoney["player_id"]
    );
    $game->setGameStateValue(
      "creeperToResolveCardId",
      $taxesAndMoney["tax_card"]["id"]
    );

    return parent::onCheckResolveKeepersAndCreepers($taxesAndMoney["tax_card"]);
  }

  public function onTurnStart()
  {
    $game = Utils::getGame();
    // if Taxes already resolved once on turn start, nothing to do
    $taxesCheckCount = $game->getGameStateValue("creeperTurnStartMoneyKept");
    if ($taxesCheckCount >= 1) {
      return null;
    }

    return $this->checkResolveTaxesAndMoney(true);
  }

  public function onCheckResolveKeepersAndCreepers($lastPlayedCard)
  {
    $game = Utils::getGame();
    // don't check Taxes again after resolving Taxes itself
    $creeperResolving = $game->getGameStateValue("creeperToResolveCardId");
    if ($lastPlayedCard != null && $lastPlayedCard["id"] == $creeperResolving) {
      return null;
    }

    // Attempt to not have to ask player to pay Taxes after every single play
    // Probably it is sufficient to ask on turn start, and then only if
    // certain cards have been played like Money or Taxes itself, goal changes,
    // keepers/creepers moved around

    $interestingCards = [
      7 => "Money",
      52 => "Taxes",
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
      return $this->checkResolveTaxesAndMoney(false);
    }

    return null;
  }

  public function resolvedBy($player_id, $args)
  {
    $game = Utils::getGame();
    $payTaxesChoice = $args["value"];

    if ($payTaxesChoice != "pay") {
      // player decided to keep their Money (and Taxes)
      $game->incGameStateValue("creeperTurnStartMoneyKept", 1);
      return;
    }
    
    $taxesAndMoney = $this->findPlayerWithTaxesAndMoney();

    if ($taxesAndMoney == null || $taxesAndMoney["player_id"] != $player_id) {
      Utils::throwInvalidUserAction(
        starfluxx::totranslate("You don't have Taxes and Money in play")
      );
    }

    $cardTaxes = $taxesAndMoney["tax_card"];
    $cardMoney = $taxesAndMoney["money_card"];
    $game->cards->playCard($cardTaxes["id"]);
    $game->cards->playCard($cardMoney["id"]);

    $players = $game->loadPlayersBasicInfos();
    $taxes_player_id = $taxesAndMoney["player_id"];
    $taxes_player_name = $players[$taxes_player_id]["player_name"];

    $game->notifyAllPlayers(
      "keepersDiscarded",
      clienttranslate('${player_name} pays <b>${card_name}</b> with Money'),
      [
        "i18n" => ["card_name"],
        "player_name" => $taxes_player_name,
        "card_name" => $this->name,
        "cards" => [$cardTaxes, $cardMoney],
        "player_id" => $taxes_player_id,
        "discardCount" => $game->cards->countCardInLocation("discard"),
        "creeperCount" => Utils::getPlayerCreeperCount($taxes_player_id),
      ]
    );
  }
}
