define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.states.goalCleaning", null, {
    constructor() {},

    onEnteringStateGoalCleaning: function (args) {
      console.log("Entering state: GoalCleaning", this.isCurrentPlayerActive(), args);

      if (this.isCurrentPlayerActive()) {
        this.goalsStock.setSelectionMode(1);

        // Let's prevent registering this listener twice
        if (this._listener !== undefined) dojo.disconnect(this._listener);

        this._listener = dojo.connect(
          this.goalsStock,
          "onChangeSelection",
          this,
          "onSelectCardGoalCleaning"
        );
      }
    },

    onLeavingStateGoalCleaning: function () {
      console.log("Leaving state: GoalCleaning");

      if (this._listener !== undefined) {
        dojo.disconnect(this._listener);
        delete this._listener;
      }
      this.goalsStock.setSelectionMode(0);
    },
    onUpdateActionButtonsGoalCleaning: function (args) {
      console.log("Update Action Buttons: GoalCleaning");
    },

    onSelectCardGoalCleaning: function () {
      var action = "discardGoal";
      var items = this.goalsStock.getSelectedItems();

      console.log("onSelectHandGoalCleaning", items);

      if (items.length == 0) return;

      if (this.checkAction(action, true)) {
        // Play a card
        this.ajaxAction(action, {
          card_id: items[0].id,
          lock: true,
        });
      }

      this.goalsStock.unselectAll();
    },
  });
});
