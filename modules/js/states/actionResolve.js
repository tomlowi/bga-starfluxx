define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.actionResolve", null, {
    constructor() {
      this._notifications.push(["actionResolved", null]);

      this._listeners = [];
    },

    onEnteringStateActionResolve: function (args) {
      console.log("Entering state: ActionResolve", args);
    },

    onUpdateActionButtonsActionResolve: function (args) {
      console.log("Update Action Buttons: ActionResolve", args);

      if (this.isCurrentPlayerActive()) {
        this.displayHelpMessage(_(args.action_help), "action");
        method = this.updateActionButtonsActionResolve[args.action_type];
        method(this, args.action_name, args.action_args);
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

    updateActionButtonsActionResolve: {
      keepersExchange: function (that, action_name, args) {
        for (var player_id in that.keepersStock) {
          var stock = that.keepersStock[player_id];
          stock.setSelectionMode(1);
        }
        that.addActionButton(
          "button_confirm",
          _("Done"),
          "onResolveActionKeepersExchange"
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
              "onResolveActionCardSelection"
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
            "onResolveActionCardSelection"
          );
        }
      },
      keeperAndPlayerSelectionAny: function (that, action_name, args) {
        for (var player_id in that.keepersStock) {
          var stock = that.keepersStock[player_id];
          stock.setSelectionMode(1);
        }

        that.addPlayerSelectionButtons(
          that,
          args,
          "onResolveActionKeeperAndPlayerSelection",
          true
        );
      },
      playerSelection: function (that, action_name, args) {
        that.addPlayerSelectionButtons(
          that,
          args,
          "onResolveActionPlayerSelection",
          false
        );
      },
      discardSelection: function (that, action_name, args) {
        dojo.place("<h3>" + action_name + "</h3>", "tmpSelectCards");
        dojo.place('<div id="tmpSelectStock"></div>', "tmpSelectCards");

        that.tmpSelectStock = that.createCardStock("tmpSelectStock", [
          "rule",
          "action",
        ]);
        that.adaptCardOverlapsForStock(that.tmpSelectStock, 4);

        that.addCardsToStock(that.tmpSelectStock, args.discard);
        that.tmpSelectStock.setSelectionMode(1);

        that._listeners["tmpDiscard"] = dojo.connect(
          that.tmpSelectStock,
          "onChangeSelection",
          that,
          "onResolveActionCardSelection"
        );
      },
      rulesSelection: function (that, action_name, args) {
        for (var rule_type in that.rulesStock) {
          var stock = that.rulesStock[rule_type];
          stock.setSelectionMode(2);
        }
        that.addActionButton(
          "button_confirm",
          _("Done"),
          "onResolveActionRulesSelection"
        );
        dojo.attr("button_confirm", "data-count", args.toDiscardCount);
      },
      ruleSelection: function (that, action_name, args) {
        for (var rule_type in that.rulesStock) {
          var stock = that.rulesStock[rule_type];
          stock.setSelectionMode(1);

          if (that._listeners["rules_" + rule_type] !== undefined) {
            dojo.disconnect(that._listeners["rules_" + rule_type]);
          }
          that._listeners["rules_" + rule_type] = dojo.connect(
            stock,
            "onChangeSelection",
            that,
            "onResolveActionCardSelection"
          );
        }
      },
      cardSelection: function (that, action_name, args) {
        that.goalsStock.setSelectionMode(1);
        if (that._listeners["goal"] !== undefined) {
          dojo.disconnect(that._listeners["goal"]);
        }
        that._listeners["goal"] = dojo.connect(
          that.goalsStock,
          "onChangeSelection",
          that,
          "onResolveActionCardSelection"
        );

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
            "onResolveActionCardSelection"
          );
        }

        for (var rule_type in that.rulesStock) {
          var stock = that.rulesStock[rule_type];
          stock.setSelectionMode(1);

          if (that._listeners["rules_" + rule_type] !== undefined) {
            dojo.disconnect(that._listeners["rules_" + rule_type]);
          }
          that._listeners["rules_" + rule_type] = dojo.connect(
            stock,
            "onChangeSelection",
            that,
            "onResolveActionCardSelection"
          );
        }
      },
      buttons: function (that, action_name, args) {
        for (var choice of args) {
          that.addActionButton(
            "button_" + choice.value,
            _(choice.label),
            "onResolveActionButtons"
          );
          dojo.attr("button_" + choice.value, "data-value", choice.value);
        }
      },
      tmpCardsSelectionForPlayer: function (that, action_name, args) {
        dojo.place("<h3>" + action_name + "</h3>", "tmpSelectCards");
        dojo.place('<div id="tmpSelectStock"></div>', "tmpSelectCards");

        that.tmpSelectStock = that.createCardStock("tmpSelectStock", [
          "keeper",
          "goal",
          "rule",
          "action",
        ]);

        that.addCardsToStock(that.tmpSelectStock, args.cards);
        that.tmpSelectStock.setSelectionMode(args.cardsPerPlayer == 1 ? 1 : 2);

        var player_id = args.forPlayerId;
        var player_name = that.players[player_id]["name"];
        if (that.player_id == player_id) {
          player_name = _("myself");
        }

        that.addActionButton(
          "button_" + player_id,
          _("for") + " " + player_name,
          "onResolveActionCardsSelection"
        );
        dojo.attr("button_" + player_id, "data-player-id", player_id);
      },

      // NotImplemented: function (that, action_name, args) {
      //   that.addActionButton(
      //     "button_0",
      //     _("Not implemented"),
      //     "onResolveActionButtons"
      //   );
      //   dojo.attr("button_0", "data-value", 0);
      // },
    },

    onResolveActionPlayerSelection: function (ev) {
      var player_id = ev.target.getAttribute("data-player-id");

      var action = "resolveActionPlayerSelection";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          player_id: player_id,
        });
      }
    },

    onResolveActionKeeperAndPlayerSelection: function (ev) {
      var selected_player_id = ev.target.getAttribute("data-player-id");

      var action = "resolveActionCardAndPlayerSelection";

      var selectedKeeper = undefined;
      for (var player_id in this.keepersStock) {
        var stock = this.keepersStock[player_id];
        var items = stock.getSelectedItems();

        if (
          items.length > 1 ||
          (items.length > 0 && selectedKeeper !== undefined)
        ) {
          this.showMessage(
            _("You must select exactly 1 item from 1 player's keeper section"),
            "error"
          );
          return;
        }

        if (items.length > 0) {
          selectedKeeper = items[0];
        }
      }

      if (selectedKeeper === undefined) {
        this.showMessage(
          _("You must select exactly 1 item from 1 player's keeper section"),
          "error"
        );
        return;
      }

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          player_id: selected_player_id,
          card_id: selectedKeeper.id,
        });
      }
    },

    onResolveActionCardSelection: function (control_name, item_id) {
      var stock = this._allStocks[control_name];

      var action = "resolveActionCardSelection";
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

    onResolveActionButtons: function (ev) {
      var value = ev.target.getAttribute("data-value");

      var action = "resolveActionButtons";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          value: value,
        });
      }
    },

    onResolveActionKeepersExchange: function (ev) {
      var myKeeper = this.keepersStock[this.player_id].getSelectedItems()[0];

      if (myKeeper === undefined) {
        this.showMessage(_("You must select one of your keepers"), "error");
        return;
      }

      var otherKeeper;

      for (var player_id in this.keepersStock) {
        if (player_id != this.player_id) {
          var stock = this.keepersStock[player_id];
          var items = stock.getSelectedItems();

          if (items.length > 0) {
            if (otherKeeper !== undefined) {
              this.showMessage(
                _("You must select only one other player's keeper"),
                "error"
              );
              return;
            }

            otherKeeper = items[0];
          }
        }
      }

      if (otherKeeper === undefined) {
        this.showMessage(
          _("You must select exactly one other player's keeper"),
          "error"
        );
        return;
      }

      var action = "resolveActionKeepersExchange";

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          myKeeperId: myKeeper.id,
          otherKeeperId: otherKeeper.id,
        });
      }
    },

    onResolveActionRulesSelection: function (ev) {
      var toDiscardCount = parseInt(ev.target.getAttribute("data-count"));
      var rules = [];

      for (var rule_type in this.rulesStock) {
        var stock = this.rulesStock[rule_type];
        rules = rules.concat(stock.getSelectedItems());
      }

      if (rules.length > toDiscardCount) {
        this.showMessage(
          dojo.string.substitute(_("You can only pick up to ${nb} rules"), {
            nb: toDiscardCount,
          }),
          "error"
        );
        return;
      }

      var action = "resolveActionCardsSelection";
      var rules_id = rules.map(function (rule) {
        return rule.id;
      });

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          cards_id: rules_id.join(";"),
        });
      }
    },

    onResolveActionCardsSelection: function (ev) {
      var action = "resolveActionCardsSelection";

      var selected_player_id = ev.target.getAttribute("data-player-id");

      var cards = this.tmpSelectStock.getSelectedItems();
      var cards_id = cards.map(function (card) {
        return card.id;
      });

      if (this.checkAction(action)) {
        this.ajaxAction(action, {
          cards_id: cards_id.join(";"),
        });
      }
    },

    onLeavingStateActionResolve: function () {
      console.log("Leaving state: ActionResolve");

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

      if (this.tmpSelectStock !== undefined) {
        delete this.tmpSelectStock;
      }
      dojo.empty("tmpSelectCards");
    },

    notif_actionResolved: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;
    },
  });
});
