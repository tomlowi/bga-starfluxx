define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.actionResolveOther", null, {
    onEnteringStateActionResolveOther: function (args) {
      console.log("Entering state: ActionResolveOther", args);
    },

    onUpdateActionButtonsActionResolveOther: function (args) {
      console.log("Update Action Buttons: ActionResolveOther", args);

      if (this.isCurrentPlayerActive()) {
        this.displayHelpMessage(_(args.action_help), "action");
        method = this.updateActionButtonsActionResolveOther[args.action_type];
        method(this, args.action_name, args.action_args);
      }
    },

    updateActionButtonsActionResolveOther: {

      handCardOptionalSelection: function (that, args) {
        that.handStock.setSelectionMode(1);

        that.addActionButton(
          "button_confirm",
          _("Done"),
          "onResolveActionForOtherByHandCardSelection"
        );
      },

      keeperSelectionOther: function (that, action_name, args) {
        for (var player_id in that.keepersStock) {
          if (player_id != that.player_id) {
            var stock = that.keepersStock[player_id];
            stock.setSelectionMode(1);

            if (that._listeners["keepers_" + player_id] !== undefined) {
              dojo.disconnect(that._listeners["keepers_" + player_id]);
            }
            that._listeners["keepers_" + player_id] = dojo.connect(
              stock,
              "onChangeSelection",
              that,
              "onResolveActionForOtherByStockCardSelection"
            );
          }
        }
      },
      
    },

    onLeavingStateActionResolveOther: function () {
      console.log("Leaving state: ActionResolveOther");

      this.handStock.setSelectionMode(0);
    },

    onResolveActionForOtherByHandCardSelection: function () {
      var action = "resolveActionForOtherByCardSelection";
      var items = this.handStock.getSelectedItems();

      var card_id_selected = null;
      if (items.length > 0)
        card_id_selected = items[0].id;

      this.ajaxAction("resolveActionForOtherByCardSelection", {
        card_id: card_id_selected
      });
    },

    onResolveActionForOtherByStockCardSelection: function (control_name, item_id) {
      var stock = this._allStocks[control_name];

      var action = "resolveActionForOtherByCardSelection";
      var items = stock.getSelectedItems();

      if (items.length == 0) return;

      if (this.checkAction(action)) {
        // Play a card
        this.ajaxAction(action, {
          card_id: items[0].id,
          lock: true,
        });
      }

      stock.unselectAll();
    },

  });
});
