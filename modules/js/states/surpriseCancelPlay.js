define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.surpriseCancelPlay", null, {
    
    onEnteringStateSurpriseCancelSurprise: function (stateArgs) {
      console.log("Entering state: SurpriseCancelSurprise", stateArgs);

      dojo.empty("tmpSelectCards");
      var tmpStockId = "tmpSurpriseStock";
      dojo.place("<h3>" + _("Surprise Queue") + "</h3>", "tmpSelectCards");
      dojo.place('<div id="tmpSurpriseStock"></div>', "tmpSelectCards");
      
      // show the cards waiting in the Surprise "queue" to everyone
      var args = stateArgs.args;
      var tmpSurpriseStock = this.createCardStock(tmpStockId, [
        "keeper",
        "goal",
        "rule",
        "action"
      ]);
      this.addCardsToStock(tmpSurpriseStock, args.surpriseCards, true, false);
      tmpSurpriseStock.setSelectionMode(0);

      for (var card_index in args.surpriseCards) {
        var card = args.surpriseCards[card_index];
        var owner_id = card.location_arg % 100000000000;
        this.display_surpriseCardOwner(tmpStockId, card.id, owner_id);
      }      
    },

    onUpdateActionButtonsSurpriseCancelSurprise: function (args) {
      console.log("Update Action Buttons: SurpriseCancelSurprise", args);

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(1);

        // Prevent registering this listener twice
        if (this._listener !== undefined) dojo.disconnect(this._listener);

        this._listener = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardSurpriseCancelSurprise"
        );

        this.addActionButton(
          "button_1",
          _("Retain Surprise"),
          "onNoSurpriseCancelSurprise"
        );
      }
    },

    onLeavingStateSurpriseCancelSurprise: function () {
      console.log("Leaving state: SurpriseCancelSurprise");

      if (this.tmpSurpriseStock !== undefined) {
        delete this.tmpSurpriseStock;
      }
      dojo.empty("tmpSelectCards");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
      }
      this.handStock.setSelectionMode(0);
      delete this._discardCount;
    },

    onSelectCardSurpriseCancelSurprise: function () {
      var action = "decideSurpriseCancelSurprise";
      var items = this.handStock.getSelectedItems();

      console.log("decideSurpriseCancelSurprise: yes", items, this.currentState);

      if (items.length == 0) return;

      this.ajaxAction("decideSurpriseCancelSurprise", {
        card_id: items[0].id,
      });
    },

    onNoSurpriseCancelSurprise: function () {
      this.ajaxAction("decideSurpriseCancelSurprise", {
        card_id: null,
      });
    },
  });
});
