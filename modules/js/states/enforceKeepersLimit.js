define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.enforceKeepersLimit", null, {
    onEnteringStateEnforceKeepersLimit: function (args) {
      console.log("Entering state: EnforceKeepersLimit", args);
    },

    onUpdateActionButtonsEnforceKeepersLimit: function (args) {
      console.log("Update Action Buttons: EnforceKeepersLimit", args);

      var stock = this.keepersStock[this.player_id];
      if (this.isCurrentPlayerActive()) {
        stock.setSelectionMode(2);

        this._discardCount = 0;
        if (args._private !== undefined) {
          this._discardCount = args._private.discardCount;
        }

        if (this._listener !== undefined) dojo.disconnect(this._listener);
        this._listener = dojo.connect(
          stock,
          "onChangeSelection",
          this,
          "onSelectCardEnforceKeepersLimit"
        );

        this.addActionButton(
          "button_1",
          _("Remove selected"),
          "onRemoveCardsEnforceKeepersLimit"
        );
      }
    },

    onLeavingStateEnforceKeepersLimit: function () {
      if (this.isSpectator) return;

      var stock = this.keepersStock[this.player_id];
      console.log("Leaving state: EnforceKeepersLimit");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
      }
      stock.setSelectionMode(0);
      delete this._discardCount;
    },

    onSelectCardEnforceKeepersLimit: function () {
      var stock = this.keepersStock[this.player_id];

      var action = "discardKeepers";
      var items = stock.getSelectedItems();

      console.log("onSelectCardKeepers", items, this.currentState);

      if (items.length == 0) return;

      if (!this.checkAction(action, true)) {
        stock.unselectAll();
      }
    },

    onRemoveCardsEnforceKeepersLimit: function () {
      var cards = this.keepersStock[this.player_id].getSelectedItems();

      if (cards.length != this._discardCount) {
        this.showMessage(
          _("You must discard the right amount of keepers!"),
          "error"
        );
        return;
      }

      var card_ids = cards.map(function (card) {
        return card.id;
      });

      console.log("discard from keepers:", card_ids);
      this.ajaxAction("discardKeepers", {
        card_ids: card_ids.join(";"),
      });
    },
  });
});
