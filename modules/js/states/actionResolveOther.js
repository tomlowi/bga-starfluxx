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
          "onResolveActionForOtherByCardSelection"
        );
      },
      
    },

    onLeavingStateActionResolveOther: function () {
      console.log("Leaving state: ActionResolveOther");

      this.handStock.setSelectionMode(0);
    },

    onResolveActionForOtherByCardSelection: function () {
      var action = "resolveActionForOtherByCardSelection";
      var items = this.handStock.getSelectedItems();

      var card_id_selected = null;
      if (items.length > 0)
        card_id_selected = items[0].id;

      this.ajaxAction("resolveActionForOtherByCardSelection", {
        card_id: card_id_selected
      });
    },

  });
});
