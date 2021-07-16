<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * starfluxx implementation : © Iwan Tomlow <iwan.tomlow@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * starfluxx.action.php
 *
 * starfluxx main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/starfluxx/starfluxx/myAction.html", ...)
 *
 */

class action_starfluxx extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg("notifwindow")) {
      $this->view = "common_notifwindow";
      $this->viewArgs["table"] = self::getArg("table", AT_posint, true);
    } else {
      $this->view = "starfluxx_starfluxx";
      self::trace("Complete reinitialization of board game");
    }
  }

  public function playCard()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, true);
    $this->game->action_playCard($card_id);
    self::ajaxResponse();
  }

  public function playFreeRule()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, true);
    $this->game->action_playFreeRule($card_id);
    self::ajaxResponse();
  }

  public function finishTurn()
  {
    self::setAjaxMode();
    $this->game->action_finishTurn();
    self::ajaxResponse();
  }

  public function stripListOfCardIds($card_ids_raw)
  {
    // Removing last ';' if exists
    if (substr($card_ids_raw, -1) == ";") {
      $card_ids_raw = substr($card_ids_raw, 0, -1);
    }
    if ($card_ids_raw == "") {
      $card_ids = [];
    } else {
      $card_ids = explode(";", $card_ids_raw);
    }
    return $card_ids;
  }

  public function discardHandCardsExcept()
  {
    self::setAjaxMode();
    $card_ids_raw = self::getArg("card_ids", AT_numberlist, true); // ids of hand cards to KEEP
    $result = $this->game->action_discardHandCardsExcept(
      $this->stripListOfCardIds($card_ids_raw)
    );
    self::ajaxResponse();
  }

  public function discardKeepers()
  {
    self::setAjaxMode();
    $card_ids_raw = self::getArg("card_ids", AT_numberlist, true); // ids of keeper cards to REMOVE
    $result = $this->game->action_discardKeepers(
      $this->stripListOfCardIds($card_ids_raw)
    );
    self::ajaxResponse();
  }

  public function discardGoal()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, true);
    $this->game->action_discardGoal($card_id);
    self::ajaxResponse();
  }

  public function resolveActionPlayerSelection()
  {
    self::setAjaxMode();
    $player_id = self::getArg("player_id", AT_posint, true);
    $this->game->action_resolveActionPlayerSelection($player_id);
    self::ajaxResponse();
  }

  public function resolveActionCardAndPlayerSelection()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, true);
    $player_id = self::getArg("player_id", AT_posint, true);
    $this->game->action_resolveActionCardAndPlayerSelection(
      $card_id,
      $player_id
    );
    self::ajaxResponse();
  }

  public function resolveActionCardSelection()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, true);
    $this->game->action_resolveActionCardSelection($card_id);
    self::ajaxResponse();
  }
  public function resolveActionCardsSelection()
  {
    self::setAjaxMode();
    $cards_id = self::getArg("cards_id", AT_numberlist, true); // ids of card to discard
    $this->game->action_resolveActionCardsSelection(
      $this->stripListOfCardIds($cards_id)
    );
    self::ajaxResponse();
  }

  public function resolveActionKeepersExchange()
  {
    self::setAjaxMode();
    $myKeeperId = self::getArg("myKeeperId", AT_posint, true);
    $otherKeeperId = self::getArg("otherKeeperId", AT_posint, true);
    $this->game->action_resolveActionKeepersExchange(
      $myKeeperId,
      $otherKeeperId
    );
    self::ajaxResponse();
  }

  public function resolveActionButtons()
  {
    self::setAjaxMode();
    $value = self::getArg("value", AT_alphanum, true);
    $this->game->action_resolveActionButtons($value);
    self::ajaxResponse();
  }

  public function resolveFreeRuleCardSelection()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, true);
    $this->game->action_resolveFreeRuleCardSelection($card_id);
    self::ajaxResponse();
  }

  public function resolveFreeRuleCardsSelection()
  {
    self::setAjaxMode();
    $cards_id = self::getArg("cards_id", AT_numberlist, true); // ids of card to discard
    $this->game->action_resolveFreeRuleCardsSelection(
      $this->stripListOfCardIds($cards_id)
    );
    self::ajaxResponse();
  }

  public function resolveFreeRulePlayerSelection()
  {
    self::setAjaxMode();
    $player_id = self::getArg("player_id", AT_posint, true);
    $this->game->action_resolveFreeRulePlayerSelection($player_id);
    self::ajaxResponse();
  }

  public function resolveFreeRuleCardAndPlayerSelection()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, true);
    $player_id = self::getArg("player_id", AT_posint, true);
    $this->game->action_resolveFreeRuleCardAndPlayerSelection(
      $card_id,
      $player_id
    );
    self::ajaxResponse();
  }

  public function resolveCreeperCardSelection()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, true);
    $this->game->action_resolveCreeperCardSelection($card_id);
    self::ajaxResponse();
  }

  public function resolveCreeperPlayerSelection()
  {
    self::setAjaxMode();
    $player_id = self::getArg("player_id", AT_posint, true);
    $this->game->action_resolveCreeperPlayerSelection($player_id);
    self::ajaxResponse();
  }

  public function resolveCreeperButtons()
  {
    self::setAjaxMode();
    $value = self::getArg("value", AT_alphanum, true);
    $this->game->action_resolveCreeperButtons($value);
    self::ajaxResponse();
  }

  public function selectTempHandCard()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, true);
    $this->game->action_selectTempHandCard($card_id);
    self::ajaxResponse();
  }

  public function decideSurpriseCounterPlay()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, false);
    $this->game->action_decideSurpriseCounterPlay($card_id);
    self::ajaxResponse();
  }

  public function decideSurpriseCancelSurprise()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, false);
    $this->game->action_decideSurpriseCancelSurprise($card_id);
    self::ajaxResponse();
  }

  public function resolveActionForOtherByCardSelection()
  {
    self::setAjaxMode();
    $card_id = self::getArg("card_id", AT_posint, false);
    $this->game->action_resolveActionForOtherByCardSelection($card_id);
    self::ajaxResponse();
  }
}
