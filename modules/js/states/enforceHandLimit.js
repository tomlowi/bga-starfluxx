define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.enforceHandLimit", null, {
    onEnteringStateEnforceHandLimit: function (args) {
      console.log("Entering state: EnforceHandLimit", args);
    },

    onUpdateActionButtonsEnforceHandLimit: function (args) {
      console.log("Update Action Buttons: EnforceHandLimit", args);

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(2);

        this._discardCount = 0;
        if (args._private !== undefined) {
          this._discardCount = args._private.discardCount;
        }

        // Prevent registering this listener twice
        if (this._listener !== undefined) dojo.disconnect(this._listener);

        this._listener = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardEnforceHandLimit"
        );

        this.addActionButton(
          "button_1",
          _("Keep selected"),
          "onRemoveCardsEnforceHandLimit"
        );
      }
    },

    onLeavingStateEnforceHandLimit: function () {
      console.log("Leaving state: EnforceHandLimit");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
      }
      this.handStock.setSelectionMode(0);
      delete this._discardCount;
    },

    onSelectCardEnforceHandLimit: function () {
      var action = "discardHandCardsExcept";
      var items = this.handStock.getSelectedItems();

      console.log("onSelectHandCard", items, this.currentState);

      if (items.length == 0) return;

      if (!this.checkAction(action, true)) {
        this.handStock.unselectAll();
      }
    },

    onRemoveCardsEnforceHandLimit: function () {
      var handSize = this.handStock.items.length;
      var cards = this.handStock.getSelectedItems();

      if (handSize - cards.length != this._discardCount) {
        this.showMessage(
          _("You must discard the right amount of cards!"),
          "error"
        );
        return;
      }

      var card_ids = cards.map(function (card) {
        return card.id;
      });

      console.log("hand limit, discard all except:", card_ids);
      this.ajaxAction("discardHandCardsExcept", {
        card_ids: card_ids.join(";"),
      });
    },
  });
});
