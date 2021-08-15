define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
  return declare("starfluxx.cardTrait", null, {
    constructor() {
      this._notifications.push(
        ["cardsDrawn", null],
        ["cardsDrawnOther", null],
        ["keeperPlayed", 500],
        ["creeperPlayed", 500],
        ["goalsDiscarded", 500],
        ["goalPlayed", null],
        ["rulesDiscarded", 500],
        ["rulePlayed", null],
        ["actionPlayed", 500],
        ["handDiscarded", 500],
        ["keepersDiscarded", 500],
        ["cardsReceivedFromPlayer", 500],
        ["cardsSentToPlayer", null],
        ["keepersMoved", 500],
        ["cardFromTableToHand", 500],
        ["handCountUpdate", null],
        ["reshuffle", null],
        ["tmpHandDiscarded", 500],
        ["forcedCardNotification", null],
        ["cardTakenFromDiscard", null],
        ["creeperAttached", 1000],
        ["creeperDetached", 1000]
      );
    },

    playCard: function (player_id, card, destinationStock) {
      // check the card exists in hand stock (might also have been in temp hand)
      var playFromHand = false;

      // forced plays (like creepers) can happen during "game" type states,
      // in which case isCurrentPlayerActive is not set
      if (player_id == this.player_id) {
        var fromDiv = this.handStock.getItemDivId(card.id);
        playFromHand = dojo.byId(fromDiv) != null;
      }

      if (playFromHand) {
        destinationStock.addToStockWithId(
          card.type_arg,
          card.id,
          this.handStock.getItemDivId(card.id)
        );
        this.handStock.removeFromStockById(card.id);
      } else {
        destinationStock.addToStockWithId(
          card.type_arg,
          card.id,
          "player_board_" + player_id
        );
      }
    },

    discardCard: function (card, stock, player_id) {
      // The new card should be on top (=first) in the discard pile
      this.discardStock.changeItemsWeight({
        [card.type_arg]: this.discardStock.count() + 1000,
      });

      var origin = "player_boards";
      var originStock = null;
      if (typeof stock !== "undefined") {
        originStock = stock.getItemDivId(card.id);
        if (originStock != null)
          origin = originStock;
      }
      
      if (originStock == null && typeof player_id !== "undefined") {
        if (player_id > this.gamedatas.offsetPlayerLocationArg)
          console.log("invalid player arg in discard", player_id, card);
        else
          origin = "player_board_" + player_id;
      }

      this.discardStock.addToStockWithId(card.type_arg, card.id, origin);

      if (originStock != null) {
        stock.removeFromStockById(card.id);
      }
    },

    discardCards: function (cards, stock, player_id) {
      var that = this;
      var cards_array = [];
      for (var card_id in cards) {
        cards_array.push(cards[card_id]);
      }

      var count = 0;
      cards_array.forEach((card) => {
        if (player_id !== undefined) {
          setTimeout(function () {
            that.discardCard(card, stock, player_id);
          }, count++ * 250);
        } else {
          that.discardCard(card, stock, player_id);
        }
      });
    },

    notif_cardsDrawn: function (notif) {
      const markerClass = "newDrawnCard";
      dojo.query("." + markerClass).removeClass(markerClass);
      for (var card of notif.args.cards) {
        this.handStock.addToStockWithId(card.type_arg, card.id, "deckCard");
        dojo.addClass("handStock_item_" + card.id, markerClass);
      }

      // Determine card overlaps per number of cards in hand / stocks
      this.adaptForScreenSize();
    },

    notif_cardsDrawnOther: function (notif) {
      var player_id = notif.args.player_id;

      if (player_id != this.player_id) {
        this.slideTemporaryObject(
          '<div class="flx-card flx-deck-card"></div>',
          "flxTable",
          "deckCard",
          "player_board_" + player_id
        );
      }

      this.handCounter[player_id].toValue(notif.args.handCount);
      this.deckCounter.toValue(notif.args.deckCount);

      if (notif.args.deckCount == 0) {
        dojo.addClass("deckCard", "flx-deck-empty");
      } else {
        dojo.removeClass("deckCard", "flx-deck-empty");
      }
    },

    notif_keeperPlayed: function (notif) {
      var player_id = notif.args.player_id;
      this.playCard(player_id, notif.args.card, this.keepersStock[player_id]);
      this.handCounter[player_id].toValue(notif.args.handCount);
      this.keepersCounter[player_id].toValue(
        this.keepersStock[player_id].count() - notif.args.creeperCount
      );
      this.creepersCounter[player_id].toValue(notif.args.creeperCount);

      this.addToKeeperPanelIcons(player_id, [notif.args.card]);
      this.adaptForScreenSize();
    },

    notif_creeperPlayed: function (notif) {
      var player_id = notif.args.player_id;
      this.playCard(player_id, notif.args.card, this.keepersStock[player_id]);
      this.handCounter[player_id].toValue(notif.args.handCount);
      this.creepersCounter[player_id].toValue(notif.args.creeperCount);

      this.addToKeeperPanelIcons(player_id, [notif.args.card]);
    },

    notif_goalsDiscarded: function (notif) {
      this.discardCards(notif.args.cards, this.goalsStock);
      this.discardCounter.toValue(notif.args.discardCount);
    },

    notif_goalPlayed: function (notif) {
      var player_id = notif.args.player_id;
      this.playCard(player_id, notif.args.card, this.goalsStock);
      this.handCounter[player_id].toValue(notif.args.handCount);
    },

    notif_rulesDiscarded: function (notif) {
      var cards = notif.args.cards;

      var drawRuleCards = {};
      var playRuleCards = {};
      var limitsRuleCards = {};
      var othersRuleCards = {};

      for (var card_id in cards) {
        var card = cards[card_id];
        if (card["location_arg"] == 0)
          // RULE_PLAY_RULE
          playRuleCards[card_id] = card;
        else if (card["location_arg"] == 1)
          // RULE_DRAW_RULE
          drawRuleCards[card_id] = card;
        else if (card["location_arg"] == 2)
          // RULE_HAND_LIMIT
          limitsRuleCards[card_id] = card;
        else if (card["location_arg"] == 3)
          // RULE_KEEPERS_LIMIT
          limitsRuleCards[card_id] = card;
        else othersRuleCards[card_id] = card;
      }

      this.discardCards(playRuleCards, this.rulesStock.playRule);
      this.discardCards(drawRuleCards, this.rulesStock.drawRule);
      this.discardCards(limitsRuleCards, this.rulesStock.limits);
      this.discardCards(othersRuleCards, this.rulesStock.others);
      this.discardCounter.toValue(notif.args.discardCount);
    },

    notif_rulePlayed: function (notif) {
      var player_id = notif.args.player_id;

      var ruleType = notif.args.ruleType;
      if (ruleType == "handLimit" || ruleType == "keepersLimit") {
        ruleType = "limits";
      } else if (ruleType != "drawRule" && ruleType != "playRule") {
        ruleType = "others";
      }

      this.playCard(player_id, notif.args.card, this.rulesStock[ruleType]);
      this.handCounter[player_id].toValue(notif.args.handCount);
    },

    notif_actionPlayed: function (notif) {
      var player_id = notif.args.player_id;
      var card = notif.args.card;
      var handCount = notif.args.handCount;
      var discardCount = notif.args.discardCount;

      var discardFromHand = false;
      if (this.isCurrentPlayerActive()) {
        var fromDiv = this.handStock.getItemDivId(card.id);
        discardFromHand = dojo.byId(fromDiv) != null;
      }

      if (discardFromHand) {
        this.discardCard(card, this.handStock, player_id);
      } else {
        this.discardCard(card, undefined, player_id);
      }
      this.handCounter[player_id].toValue(handCount);
      this.discardCounter.toValue(discardCount);
    },

    notif_handDiscarded: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      if (player_id == this.player_id) {        
        this.discardCards(cards, this.handStock);
      } else {
        this.discardCards(cards, undefined, player_id);
      }

      this.handCounter[player_id].toValue(notif.args.handCount);
      this.discardCounter.toValue(notif.args.discardCount);
    },

    notif_keepersDiscarded: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      this.discardCards(cards, this.keepersStock[player_id]);

      this.keepersCounter[player_id].toValue(
        this.keepersStock[player_id].count() - notif.args.creeperCount
      );
      this.creepersCounter[player_id].toValue(notif.args.creeperCount);
      this.discardCounter.toValue(notif.args.discardCount);

      this.removeFromKeeperPanelIcons(player_id, cards);
    },

    notif_cardsReceivedFromPlayer: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      for (var card_id in cards) {
        var card = cards[card_id];
        this.handStock.addToStockWithId(
          card.type_arg,
          card.id,
          "player_board_" + player_id
        );
      }
    },

    notif_cardsSentToPlayer: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      for (var card_id in cards) {
        var card = cards[card_id];
        this.handStock.removeFromStockById(
          card.id,
          "player_board_" + player_id,
          true
        );
      }
      this.handStock.updateDisplay();
    },

    notif_keepersMoved: function (notif) {
      console.log(notif.args);
      var destination_player_id = notif.args.destination_player_id;
      var origin_player_id = notif.args.origin_player_id;
      var cards = notif.args.cards;

      var originStock = this.keepersStock[origin_player_id];
      var destinationStock = this.keepersStock[destination_player_id];

      for (var card_id in cards) {
        var card = cards[card_id];
        destinationStock.addToStockWithId(
          card.type_arg,
          card.id,
          originStock.getItemDivId(card.id)
        );
        originStock.removeFromStockById(card.id);
      }
      this.keepersCounter[destination_player_id].toValue(
        destinationStock.count() - notif.args.destination_creeperCount
      );
      this.keepersCounter[origin_player_id].toValue(
        originStock.count() - notif.args.origin_creeperCount
      );
      this.creepersCounter[destination_player_id].toValue(
        notif.args.destination_creeperCount
      );
      this.creepersCounter[origin_player_id].toValue(
        notif.args.origin_creeperCount
      );

      this.removeFromKeeperPanelIcons(origin_player_id, cards);
      this.addToKeeperPanelIcons(destination_player_id, cards);
    },

    notif_handCountUpdate: function (notif) {
      var handsCount = notif.args.handsCount;
      for (var player_id in handsCount) {
        this.handCounter[player_id].toValue(handsCount[player_id]);
      }
    },

    notif_reshuffle: function (notif) {
      this.deckCounter.toValue(notif.args.deckCount);
      dojo.removeClass("deckCard", "flx-deck-empty");

      this.discardCounter.toValue(notif.args.discardCount);

      var exceptionCards = notif.args.exceptionCards;
      if (exceptionCards === undefined) {
        this.discardStock.removeAll();
      } else {
        var exceptionCardsType = exceptionCards.map(function (card) {
          return card.type_arg;
        });
        for (var card of this.discardStock.getAllItems()) {
          if (exceptionCardsType.indexOf(card.type) == -1) {
            this.discardStock.removeFromStockById(card.id, "deckCard", true);
          } else {
            // remaining card should become bottom of discard pile
            this.discardStock.changeItemsWeight({
              [card.type]: 999,
            });
          }
        }
        this.discardStock.updateDisplay();
      }
    },

    notif_cardFromTableToHand: function (notif) {
      var player_id = notif.args.player_id;
      var card = notif.args.card;

      var originStock;

      var card_definition = this.cardsDefinitions[card.type_arg];

      switch (card.location) {
        case "keepers":
          originStock = this.keepersStock[card.location_arg];
          break;

        case "rules":
          if (card_definition.ruleType == "playRule") {
            originStock = this.rulesStock.playRule;
          } else if (card_definition.ruleType == "drawRule") {
            originStock = this.rulesStock.drawRule;
          } else if (card_definition.ruleType == "handLimit") {
            originStock = this.rulesStock.limits;
          } else if (card_definition.ruleType == "keepersLimit") {
            originStock = this.rulesStock.limits;
          } else {
            originStock = this.rulesStock.others;
          }
          break;

        case "goals":
          originStock = this.goalsStock;
          break;

        default:
          return;
      }

      if (player_id == this.player_id) {
        this.handStock.addToStockWithId(
          card.type_arg,
          card.id,
          originStock.getItemDivId(card.id)
        );
        originStock.removeFromStockById(card.id);
      } else {
        originStock.removeFromStockById(card.id, "player_board_" + player_id);
      }

      // Update the hand and keepers counts
      this.handCounter[player_id].toValue(notif.args.handCount);

      if (card.location == "keepers") {
        var from_player_id = card.location_arg;
        this.keepersCounter[from_player_id].toValue(
          this.keepersStock[from_player_id].count() - notif.args.creeperCount
        );
        this.creepersCounter[from_player_id].toValue(notif.args.creeperCount);

        this.removeFromKeeperPanelIcons(from_player_id, [card]);
      }
    },

    notif_tmpHandDiscarded: function (notif) {
      var player_id = notif.args.player_id;
      var cards = notif.args.cards;

      // tmp hand stocks will already be destroyed
      this.discardCards(cards, undefined, player_id);

      this.discardCounter.toValue(notif.args.discardCount);
    },

    notif_forcedCardNotification: function (notif) {
      var card_trigger = notif.args.card_trigger;
      var card_forced = notif.args.card_forced;

      this.showNotificationBubble(
        _(card_trigger) + ": <b>" + _(card_forced) + "</b>"
      );
      setTimeout(() => this.hideNotificationBubble(), 3000);
    },

    notif_cardTakenFromDiscard: function (notif) {
      var card = notif.args.card;
      this.discardStock.removeFromStockById(card.id);
      this.discardCounter.toValue(notif.args.discardCount);
    },

    notif_creeperAttached: function (notif) {
      var player_id = notif.args.player_id;
      var keeper_card = notif.args.card;
      var creeper = notif.args.creeper;

      this.display_creeperAttached(player_id, keeper_card["id"], creeper);
    },

    display_creeperAttached: function (
      player_id,
      keeper_id,
      creeper_unique_id,
      retryCount = 0
    ) {
      var keeperCardId = "keepersStock" + player_id + "_item_" + keeper_id;
      if (dojo.byId(keeperCardId) == null) {
        // moved keeper div might not be available in DOM yet
        if (retryCount < 1) {
          setTimeout(
            () =>
              this.display_creeperAttached(
                player_id,
                keeper_id,
                creeper_unique_id,
                retryCount + 1
              ),
            2000
          );
        }
        console.log(
          "creeperAttached cannot be shown, missing div = " + keeperCardId
        );
        return;
      }

      var creeperDivId = "flx-board-panel-keeper-" + creeper_unique_id;
      var creeperItem = dojo.byId(creeperDivId);
      var creeperAttach = dojo.clone(creeperItem);
      dojo.attr(creeperAttach, "id", creeperDivId + "-attach");

      var parentNode = dojo.query('.flx-card-overlay .flx-card-creeper-attach', keeperCardId)[0];
      dojo.place(creeperAttach, parentNode);
    },

    notif_creeperDetached: function (notif) {
      //var player_id = notif.args.player_id;
      var creeper = notif.args.creeper;

      var creeperDivId = "flx-board-panel-keeper-" + creeper + "-attach";
      var creeperDiv = dojo.byId(creeperDivId);
      if (creeperDiv != null) {
        dojo
          .fadeOut({
            duration: 1000,
            node: creeperDiv,
            onEnd: dojo.destroy,
            delay: 500,
          })
          .play();
      }
    },

    display_surpriseCardOwner: function(stock_name, card_id, player_id) {

      var divCardId = stock_name + "_item_" + card_id;
      if (dojo.byId(divCardId) == null)
        return;

      var player_owner = this.gamedatas.players[player_id];
      if (player_owner == null)
        return;
      
      var background_color = "ebd5bd"; // same as player panels
      var cardOverlayOwner = this.format_block(
        "jstpl_cardOverlay_owner",
        {
          player_name: player_owner.name,
          player_color: player_owner.color,
          back_color: background_color,
        }
      );
      dojo.place(cardOverlayOwner, divCardId);

    },

  });
});
