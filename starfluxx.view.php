<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * starfluxx implementation : © Iwan Tomlow <iwan.tomlow@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * starfluxx.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in starfluxx_starfluxx.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once APP_BASE_PATH . "view/common/game.view.php";

class view_starfluxx_starfluxx extends game_view
{
  public function getGameName()
  {
    return "starfluxx";
  }
  public function build_page($viewArgs)
  {
    $template = self::getGameName() . "_" . self::getGameName();

    // Get current player ID & all players info
    global $g_user;
    $current_player_id = $g_user->get_id();
    $players = $this->game->loadPlayersBasicInfos();

    // Translations
    $this->tpl["MY_HAND"] = self::_("My hand");
    $this->tpl["MY_KEEPERS"] = self::_("My keepers");
    $this->tpl["BASICRULES"] = self::_("Basic Rules");
    $this->tpl["BASICRULE_DRAW"] = self::_("Draw 1");
    $this->tpl["BASICRULE_PLAY"] = self::_("Play 1");
    $this->tpl["LIMITRULES"] = self::_("Limits");
    $this->tpl["OTHERRULES"] = self::_("Extra Rules");
    $this->tpl["GOAL"] = self::_("Goal");
    $this->tpl["DECK"] = self::_("Discard pile");
    $this->tpl["SHOW_DISCARD"] = self::_("Show");

    // This will inflate players keepers block
    $player_color = "#000000";
    if (array_key_exists($current_player_id, $players)) {
      $player_info = $players[$current_player_id];
      $player_color = $player_info["player_color"];
    }    
    $this->tpl["CURRENT_PLAYER_ID"] = $current_player_id;
    $this->tpl["CURRENT_PLAYER_COLOR"] = $player_color;

    $this->page->begin_block($template, "keepers");

    $players_in_order = $this->game->getPlayersInOrderForCurrentPlayer();

    foreach ($players_in_order as $player_id) {
      if ($player_id != $current_player_id) {
        $player_info = $players[$player_id];
        $this->page->insert_block("keepers", [
          "PLAYER_ID" => $player_id,
          "PLAYER_NAME" => $player_info["player_name"],
          "PLAYER_COLOR" => $player_info["player_color"],
        ]);
      }
    }
  }
}
