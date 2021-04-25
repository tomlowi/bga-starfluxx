define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.tempHandPlay", null, {
    constructor() {
      this.tmpHandStockActive = null;
      this.tmpHandStocks = [];
    },

    onEnteringStateTempHandPlay: function (args) {
      console.log("Entering state: TempHandPlay", this.isCurrentPlayerActive());
    },

    onLeavingStateTempHandPlay: function () {
      console.log("Leaving state: TempHandPlay");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
      }

      this.tmpHandStockActive = null;
      for (var tmpHandStock_id in this.tmpHandStocks) {
        delete this.tmpHandStocks[tmpHandStock_id];
        dojo.empty(tmpHandStock_id);
      }
    },

    onUpdateActionButtonsTempHandPlay: function (args) {
      console.log("Update Action Buttons: TempHandPlay", args);

      if (this.isCurrentPlayerActive()) {
        // just to be sure, some strange bug reports about "hanging" previous temp hand cards
        dojo.empty("tmpHand1");
        dojo.empty("tmpHand2");
        dojo.empty("tmpHand3");

        var tmpHandActive = args.tmpHandActive;
        for (var tmpHandId in args.tmpHands) {
          var tmpHand = args.tmpHands[tmpHandId];
          var tmpStockId = tmpHandId + "Stock";

          dojo.place("<h3>" + _(tmpHand.tmpHandName) + "</h3>", tmpHandId);
          dojo.place('<div id="' + tmpStockId + '"></div>', tmpHandId);

          var tmpHandStock = this.createCardStock(tmpStockId, [
            "keeper",
            "goal",
            "rule",
            "action",
            "creeper",
          ]);
          this.addCardsToStock(tmpHandStock, tmpHand.tmpHandCards);

          this.tmpHandStocks[tmpHandId] = tmpHandStock;
          if (tmpHandId == tmpHandActive) {
            this.tmpHandStockActive = tmpHandStock;
            this.tmpHandStockActive.setSelectionMode(1);

            this._listener = dojo.connect(
              this.tmpHandStockActive,
              "onChangeSelection",
              this,
              "onSelectCardTempHandPlay"
            );
          }
        }
      }
    },

    onSelectCardTempHandPlay: function () {
      var action = "selectTempHandCard";
      var items = this.tmpHandStockActive.getSelectedItems();

      if (items.length == 0) return;

      if (this.checkAction(action, true)) {
        // Play a card from temp hand
        this.ajaxAction(action, {
          card_id: items[0].id,
          lock: true,
        });
      }

      this.tmpHandStockActive.unselectAll();
    },
  });
});
