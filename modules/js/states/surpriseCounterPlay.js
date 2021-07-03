define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.surpriseCounterPlay", null, {
    onEnteringStateSurpriseCounterPlay: function (args) {
      console.log("Entering state: SurpriseCounterPlay", args);
    },

    onUpdateActionButtonsSurpriseCounterPlay: function (args) {
      console.log("Update Action Buttons: SurpriseCounterPlay", args);

      dojo.empty("tmpSelectCards");
      var tmpStockId = "tmpSurpriseStock";
      dojo.place("<h3>" + _("Surprise Queue") + "</h3>", "tmpSelectCards");
      dojo.place('<div id="tmpSurpriseStock"></div>', "tmpSelectCards");
      // show the cards waiting in the Surprise "queue" to everyone
      var tmpSurpriseStock = this.createCardStock(tmpStockId, [
        "keeper",
        "goal",
        "rule",
        "action"
      ]);
      this.addCardsToStock(tmpSurpriseStock, args.surpriseCards);
      tmpSurpriseStock.setSelectionMode(0);

      if (this.isCurrentPlayerActive()) {
        this.handStock.setSelectionMode(1);

        // Prevent registering this listener twice
        if (this._listener !== undefined) dojo.disconnect(this._listener);

        this._listener = dojo.connect(
          this.handStock,
          "onChangeSelection",
          this,
          "onSelectCardSurpriseCounterPlay"
        );

        this.addActionButton(
          "button_1",
          _("No Surprise"),
          "onNoSurpriseCounterPlay"
        );
      }
    },

    onLeavingStateSurpriseCounterPlay: function () {
      console.log("Leaving state: SurpriseCounterPlay");

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

    onSelectCardSurpriseCounterPlay: function () {
      var action = "decideSurpriseCounterPlay";
      var items = this.handStock.getSelectedItems();

      console.log("decideSurpriseCounterPlay: yes", items, this.currentState);

      if (items.length == 0) return;

      this.ajaxAction("decideSurpriseCounterPlay", {
        card_id: items[0].id,
      });
    },

    onNoSurpriseCounterPlay: function () {
      var handSize = this.handStock.items.length;
      var cards = this.handStock.getSelectedItems();

      var card_ids = cards.map(function (card) {
        return card.id;
      });

      console.log("decideSurpriseCounterPlay: no", card_ids);
      this.ajaxAction("decideSurpriseCounterPlay", {
        card_id: null,
      });
    },
  });
});
