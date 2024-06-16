define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.playRockPaperScissors", null, {
    constructor() {
      this._notifications.push(["resultRockPaperScissors", 500]);

      this._listeners = [];
    },

    onEnteringStatePlayRockPaperScissors: function (args) {
      console.log("Entering state: playRockPaperScissors", args);
    },

    onUpdateActionButtonsPlayRockPaperScissors: function (args) {
      console.log("Update Action Buttons: ActionResolve", args);

      if (this.isCurrentPlayerActive()) {
        // @TODO: improve UX
        this.addActionButton(
          "button_rock",
          _("Rock"),
          "onSelectRockPaperScissors"
        );
        this.addActionButton(
          "button_paper",
          _("Paper"),
          "onSelectRockPaperScissors"
        );
        this.addActionButton(
          "button_scissors",
          _("Scissors"),
          "onSelectRockPaperScissors"
        );
        dojo.attr("button_rock", "data-value", "rock");
        dojo.attr("button_paper", "data-value", "paper");
        dojo.attr("button_scissors", "data-value", "scissors");
      }
    },

    onSelectRockPaperScissors: function (ev) {
      var value = ev.target.getAttribute("data-value");

      var action = "selectRockPaperScissors";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          value: value,
        });
      }
    },

    onLeavingStatePlayRockPaperScissors: function () {
      console.log("Leaving state: PlayRockPaperScissors");
    },

    notif_resultRockPaperScissors: function (notif) {
      var args = notif.args;

      var divParent = "game_play_area";
      var divFrom1 = "overall_player_board_" + args.challenger_player_id;
      var divFrom2 = "overall_player_board_" + args.defender_player_id;
      var divTo1 = "baseRuleDraw";
      var divTo2 = "baseRulePlay";

      var challengerBlock = this.format_block("jstpl_rockPaperScissors", {
        type: "challenger",
        choice: args.challenger_choice_id,
        player_name: args.challenger_player_name,
      });

      var defenderBlock = this.format_block("jstpl_rockPaperScissors", {
        type: "defender",
        choice: args.defender_choice_id,
        player_name: args.defender_player_name,
      });

      this.slideTemporaryObject(
        challengerBlock,
        divParent,
        divFrom1,
        divTo1,
        3000,
        0
      );
      this.slideTemporaryObject(
        defenderBlock,
        divParent,
        divFrom2,
        divTo2,
        3200,
        0
      );
    },
  });
});
