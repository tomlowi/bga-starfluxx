define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.creeperResolve", null, {
    constructor() {
      //this._notifications.push(["creeperResolved", null]);

      this._listeners = [];
    },

    onEnteringStateCreeperResolve: function (args) {
      console.log("Entering state: CreeperResolve", args);
    },

    onUpdateActionButtonsCreeperResolve: function (args) {
      console.log("Update Action Buttons: CreeperResolve", args);

      if (this.isCurrentPlayerActive()) {
        this.displayHelpMessage(_(args.action_help), "creeper");
        method = this.updateActionButtonsCreeperResolve[args.action_type];
        method(this, args.action_args);
      }
    },

    addPlayerSelectionButtons(that, args, onResolveMethodName, includeSelf) {
      // @TODO: could be extended with nice visual way of selecting other players
      for (var player_id in that.players) {
        if (includeSelf || player_id != that.player_id) {
          that.addActionButton(
            "button_" + player_id,
            that.players[player_id]["name"],
            onResolveMethodName
          );
          dojo.attr("button_" + player_id, "data-player-id", player_id);
        }
      }
    },

    updateActionButtonsCreeperResolve: {
      keeperSelectionSelf: function (that, args) {
        for (var player_id in that.keepersStock) {
          if (player_id == that.player_id) {
            var stock = that.keepersStock[player_id];
            stock.setSelectionMode(1);

            if (that._listeners["keepers_" + player_id] !== undefined) {
              dojo.disconnect(that._listeners["keepers_" + player_id]);
            }
            that._listeners["keepers_" + player_id] = dojo.connect(
              stock,
              "onChangeSelection",
              that,
              "onResolveCreeperCardSelection"
            );

            if (that.keepersStock[player_id].count() <= 1) {
              // Only Death > player can choose to do nothing
              that.addActionButton(
                "button_confirm",
                _("Done"),
                "onResolveCreeperCardSelectionNothing"
              );
            }
          }
        }
      },
      playerSelection: function (that, args) {
        that.addPlayerSelectionButtons(
          that,
          args,
          "onResolveCreeperPlayerSelection",
          false
        );
      },
      buttons: function (that, args) {
        for (var choice of args) {
          that.addActionButton(
            "button_" + choice.value,
            _(choice.label),
            "onResolveCreeperButtons"
          );
          dojo.attr("button_" + choice.value, "data-value", choice.value);
        }
      },
    },

    onResolveCreeperCardSelectionNothing: function () {
      var card_id = 0;
      var action = "resolveCreeperCardSelection";
      if (this.checkAction(action)) {
        // Play a card
        this.ajaxAction(action, {
          card_id: card_id,
        });
      }
    },

    onResolveCreeperCardSelection: function (control_name, item_id) {
      var stock = this._allStocks[control_name];

      var action = "resolveCreeperCardSelection";
      var items = stock.getSelectedItems();

      if (items.length == 0) return;

      if (this.checkAction(action)) {
        // Play a card
        this.ajaxAction(action, {
          card_id: items[0].id,
        });
      }

      stock.unselectAll();
    },

    onResolveCreeperPlayerSelection: function (ev) {
      var player_id = ev.target.getAttribute("data-player-id");

      var action = "resolveCreeperPlayerSelection";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          player_id: player_id,
        });
      }
    },

    onResolveCreeperButtons: function (ev) {
      var value = ev.target.getAttribute("data-value");

      var action = "resolveCreeperButtons";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          value: value,
        });
      }
    },

    onLeavingStateCreeperResolve: function () {
      console.log("Leaving state: CreeperResolve");

      this.handStock.setSelectionMode(0);
      this.goalsStock.setSelectionMode(0);

      for (var player_id in this.keepersStock) {
        var stock = this.keepersStock[player_id];
        stock.setSelectionMode(0);
      }

      for (var rule_type in this.rulesStock) {
        var stock = this.rulesStock[rule_type];
        stock.setSelectionMode(0);
      }

      for (var listener_id in this._listeners) {
        dojo.disconnect(this._listeners[listener_id]);
        delete this._listeners[listener_id];
      }
    },

    // notif_creeperResolved: function (notif) {
    //   // nothing really needed?
    // },
  });
});
