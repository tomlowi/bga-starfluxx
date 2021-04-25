var isDebug =
  window.location.host == "studio.boardgamearena.com" ||
  window.location.hash.indexOf("debug") > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define(["dojo", "dojo/_base/declare", "ebg/core/gamegui"], (dojo, declare) => {
  return declare("customgame.game", ebg.core.gamegui, {
    /*
     * Constructor
     */
    constructor() {
      this._notifications = [];
      this._notifications.push(["win", 100]);
    },

    /*
     * Detect if spectator or replay
     */
    isReadOnly() {
      return (
        this.isSpectator || typeof g_replayFrom != "undefined" || g_archive_mode
      );
    },

    notif_win: function (notif) {
      this.myDlg = new ebg.popindialog();
      this.myDlg.create("flxWinDialog");
      this.myDlg.setTitle(_("We have a winner!"));
      this.myDlg.setMaxWidth(500); // Optional

      // Create the HTML of my dialog.
      // The best practice here is to use Javascript templates
      var html = this.format_block("jstpl_winDialogTemplate", {
        msg: this.format_string_recursive(notif.log, notif.args),
      });

      // Content must be set before calling show() so that the size of the content
      // is defined before positioning the dialog
      this.myDlg.setContent(html);

      // move the winning goal card to here
      var goalItemId = "goalsStock_item_" + notif.args.goal_id;
      var goalItem = dojo.byId(goalItemId);
      if (goalItem) {
        var winningGoal = dojo.clone(goalItem);
        dojo.place(winningGoal, "flx-win-dialog-goal");
      }

      this.myDlg.show();
    },

    changeInnerHtml: function (id, text) {
      if (dojo.byId(id)) {
        dojo.byId(id).innerHTML = text;
      }
    },

    displayHelpMessage: function (msg, msgType) {
      if ((msg || "") == "") return;

      this.changeInnerHtml(
        "flx_help_msg",
        '<span class="help_txt help_txt_' + msgType + '">' + msg + "</span>"
      );
    },

    resetHelpMessage: function () {
      this.changeInnerHtml("flx_help_msg", "");
    },

    /*
     * Bubble management
     */
    showNotificationBubble: function (message) {
      const itemId = "flxMyNotification";
      $(itemId).innerHTML = message;
      dojo.addClass(itemId, "flx-notification-bubble--visible");
    },

    hideNotificationBubble: function () {
      const itemId = "flxMyNotification";
      dojo.removeClass(itemId, "flx-notification-bubble--visible");
    },
  });
});
