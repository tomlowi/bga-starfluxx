define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.playCard", null, {
    constructor() {},

    onEnteringStatePlayCard: function (args) {
      console.log("Entering state: PlayCard", this.isCurrentPlayerActive());

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(1);

        // Let's prevent registering this listener twice
        if (this._listener !== undefined) dojo.disconnect(this._listener);

        this._listener = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardPlayCard"
        );
      }
    },

    onLeavingStatePlayCard: function () {
      console.log("Leaving state: PlayCard");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
        this.handStock.setSelectionMode(0);
      }
    },

    onUpdateActionButtonsPlayCard: function (args) {
      console.log("Update Action Buttons: PlayCard", args);

      if (args.freeRules != undefined && args.freeRules.length > 0) {
        for (availableRule of args.freeRules) {
          var card_id = availableRule.card_id;
          this.addActionButton(
            "button_rule_" + card_id,
            _(availableRule.name),
            "onPlayFreeRule"
          );
          dojo.attr("button_rule_" + card_id, "data-rule-id", card_id);
        }
      }
      // if no more cards must be played, but free rules available
      // => player should have possibility to explicitly end turn
      // otherwise turn should have passed automatically, but in special cases
      // (e.g. last action = forced play we might still get in this state with count=0)
      if (args.count == 0) {
        this.addActionButton(
          "button_finish",
          _("Finish Turn"),
          "onPlayFinishTurn"
        );
      }
    },

    onSelectCardPlayCard: function () {
      var action = "playCard";
      var items = this.handStock.getSelectedItems();

      console.log("onSelectHandPlayCard", items);

      if (items.length == 0) return;

      if (this.checkAction(action, true)) {
        // Play a card
        this.ajaxAction(action, {
          card_id: items[0].id,
          lock: true,
        });
      }

      this.handStock.unselectAll();
    },

    onPlayFreeRule: function (ev) {
      var rule_id = ev.target.getAttribute("data-rule-id");

      var action = "playFreeRule";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          card_id: rule_id,
        });
      }
    },

    onPlayFinishTurn: function (ev) {
      var action = "finishTurn";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {});
      }
    },
  });
});
