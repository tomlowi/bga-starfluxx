define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.freeRuleResolve", null, {
    constructor() {
      //this._notifications.push(["freeRuleResolved", null]);

      this._listeners = [];
    },

    onEnteringStateFreeRuleResolve: function (args) {
      console.log("Entering state: FreeRuleResolve", args);
    },

    onUpdateActionButtonsFreeRuleResolve: function (args) {
      console.log("Update Action Buttons: FreeRuleResolve", args);

      this.displayHelpMessage(_(args.action_help), "freerule");
      if (this.isCurrentPlayerActive()) {
        method = this.updateActionButtonsFreeRuleResolve[args.action_type];
        method(this, args.action_args);
      }
    },

    addPlayerSelectionButtons(that, args, onResolveMethodName, includeSelf) {
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

    updateActionButtonsFreeRuleResolve: {
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
              "onResolveFreeRuleCardSelection"
            );
          }
        }
      },
      keeperSelectionAny: function (that, action_name, args) {
        for (var player_id in that.keepersStock) {
          var stock = that.keepersStock[player_id];
          stock.setSelectionMode(1);

          if (that._listeners["keepers_" + player_id] !== undefined) {
            dojo.disconnect(that._listeners["keepers_" + player_id]);
          }
          that._listeners["keepers_" + player_id] = dojo.connect(
            stock,
            "onChangeSelection",
            that,
            "onResolveFreeRuleCardSelection"
          );
        }
      },
      handCardsSelection: function (that, args) {
        that.handStock.setSelectionMode(2);

        that.addActionButton(
          "button_confirm",
          _("Done"),
          "onResolveFreeRuleHandCardsSelection"
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
              "onResolveFreeRuleCardSelection"
            );
          }
        }
      },
      playerSelection: function (that, action_name, args) {
        that.addPlayerSelectionButtons(
          that,
          args,
          "onResolveFreeRulePlayerSelection",
          false
        );
      },
    },

    onResolveFreeRuleHandCardsSelection: function (ev) {
      var selectedCards = [];

      var stock = this.handStock;
      selectedCards = stock.getSelectedItems();

      var action = "resolveFreeRuleCardsSelection";
      var cards_id = selectedCards.map(function (card) {
        return card.id;
      });

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          cards_id: cards_id.join(";"),
        });
      }
    },

    onResolveFreeRuleCardSelection: function (control_name, item_id) {
      var stock = this._allStocks[control_name];

      var action = "resolveFreeRuleCardSelection";
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

    onResolveFreeRulePlayerSelection: function (ev) {
      var player_id = ev.target.getAttribute("data-player-id");

      var action = "resolveFreeRulePlayerSelection";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          player_id: player_id,
        });
      }
    },

    onLeavingStateFreeRuleResolve: function () {
      console.log("Leaving state: FreeRuleResolve");

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
  });
});
