{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- starfluxx implementation : © Iwan Tomlow <iwan.tomlow@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    starfluxx_starfluxx.tpl
-->

<div class="flx_help_msg_wrapper">
  <div id="flx_help_msg" class="flx_help"></div>
</div>

<div id="tmpSelectCards" class="whiteblock flx-hand flx-tmp-hand"></div>
<div id="tmpHand3" class="whiteblock flx-hand flx-tmp-hand"></div>
<div id="tmpHand2" class="whiteblock flx-hand flx-tmp-hand"></div>
<div id="tmpHand1" class="whiteblock flx-hand flx-tmp-hand"></div>

<div id="tmpKeeperPanelIcons" class="whiteblock flx-keeper-panel-placeholder"></div>

<div class="flx-player">
  <div id="flxMyNotification" class="flx-notification-bubble"></div>
  <div id="flxMyHand" class="whiteblock flx-hand">
    <h3 style="color: #{CURRENT_PLAYER_COLOR}">{MY_HAND}</h3>    
    <div id="handStock"></div>
  </div>
</div>

<div id="flxTable" class="whiteblock flx-table">

  <div class="flx-table-section flx-goal">
    <h3>{GOAL}</h3>
    <div id="goalsStock" class="flx-card-stock"></div>
  </div>

  <div id="flxDeckBlock" class="flx-table-section flx-deck">
    <h3>{DECK}</h3>
    <div class="flx-deck-wrap">
      <div class="flx-discard-deck">
        <div id="discardPileCollapsed" class="flx-discard">
          <div id="discardToggle" class="flx-toggle"><a id="discardToggleBtn" href="#">{SHOW_DISCARD}</a></div>
          <div id="discardCount" class="flx-card-count"></div>
          <div id="discardStock"></div>
        </div>
        <div id="deckCard" class="flx-card flx-deck-card">
          <div id="deckCount" class="flx-card-count"></div>
        </div>
      </div>
    </div>
  </div>


    <div class="flx-table-section flx-rules-basic">
      <h3>{BASICRULES}</h3>
      <div class="flx-rules-basic-wrap">
        <div class="flx-card-stack">
          <div id="baseRuleCard" class="flx-card-stack-center">
            <div id="baseRuleDraw" class="flx-card card-name card-name-normal">
              {BASICRULE_DRAW}
            </div>
            <div id="baseRulePlay" class="flx-card card-name card-name-normal">
              {BASICRULE_PLAY}
            </div>
          </div>
          <div id="drawRuleStock" class="flx-card-stack-left"></div>
          <div id="playRuleStock" class="flx-card-stack-right"></div>
        </div>
      </div>
    </div>

    <div class="flx-table-section flx-rules-limits">
      <h3>{LIMITRULES}</h3>
      <div id="limitsStock" class="flx-card-stock"></div>
    </div>

    <div class="flx-table-section flx-rules-other">
      <h3>{OTHERRULES}</h3>
      <div id="othersStock" class="flx-card-stock"></div>
    </div>

</div>

<div id="discardPileExpanded" class="whiteblock flx-tmp-hand"></div>

<div class="flx-other-players">
  <div id="flxMyKeepers" class="whiteblock flx-keepers flx-keepers-my">
    <h3 style="color: #{CURRENT_PLAYER_COLOR}">{MY_KEEPERS}</h3>
    <div id="keepersStock{CURRENT_PLAYER_ID}"></div>
  </div>
  <!-- BEGIN keepers -->
  <div class="whiteblock flx-keepers flx-keepers-other">
    <h3 style="color: #{PLAYER_COLOR}">{PLAYER_NAME}</h3>
    <div id="keepersStock{PLAYER_ID}"></div>
  </div>
  <!-- END keepers -->
</div>

<script>
  var jstpl_player_board =
    '\
<div class="flx-board">\
  <div class="flx-board-icons">\
    <div class="flx-board-hand">\
      <span id="handIcon${id}" class="flx-icons flx-icons-hand"\
        aria-label="${tooltipCardsInHand}" title="${tooltipCardsInHand}"></span>\
      <span id="handCount${id}"></span>\
    </div>\
    <div class="flx-board-keeper">\
      <span id="keeperIcon${id}" class="flx-icons flx-icons-keeper"\
        aria-label="${tooltipKeepersInPlay}" title="${tooltipKeepersInPlay}"></span>\
      <span id="keepersCount${id}"></span>\
    </div>\
    <div class="flx-board-creeper">\
      <span id="creeperIcon${id}" class="flx-icons flx-icons-creeper"\
        aria-label="${tooltipCreepersInPlay}" title="${tooltipCreepersInPlay}"></span>\
      <span id="creepersCount${id}"></span>\
    </div>\
  </div>\
  <div class="flx-board-keepers">\
    <div id="creeperPanel${id}"></div>\
    <div id="keeperPanel${id}"></div>\
  </div>\
</div>';

  var jstpl_cardTooltip = `<div class="flx-card-tooltip">
		<div class="flx-card flx-card-tooltip-\${type}" id="flx-card-tooltip-\${id}">
			<div class="card-front">
				<div class="card-name">\${name}</div>
        <div class="card-type">\${typeName}</div>
        <div class="card-subtype">\${subtypeName}</div>
				<div class="card-subtitle">\${subtitle}</div>
				<div class="card-description">\${description}</div>				
				<div class="card-background"></div>
			</div>
		</div>
</div>`;

var jstpl_cardOverlay_owner = `<div class="flx-card-overlay-owner">
  <span style="color: #\${player_color}">\${player_name}</span>
</div>`;

var jstpl_cardOverlay_title = `<div class="flx-card-overlay-title">
		<div class="flx-card-title flx-card-title-base flx-card-title-\${type}" 
          id="flx-card-title-\${id}">
			<div class="card-overlay-title">
        <div class="card-type">\${typeName}</div>
			</div>
		</div>
</div>`;
var jstpl_cardOverlay_text = `<div class="flx-card-overlay">
		<div class="flx-card flx-card-\${set} flx-card-\${type}" 
          id="flx-card-overlay-\${id}">
			<div class="card-overlay">
        <div class="card-name card-name-\${nameLength}">\${name}</div>
        <div class="card-subtitle">\${subtitle}</div>
				<div class="card-description card-desc-\${descLength}"">\${description}</div>
			</div>
		</div>
</div>`;

var jstpl_panel_keeper = `
		<div class="flx-board-panel-keeper" style="background-position-x: -\${offset}%"
          id="flx-board-panel-keeper-\${id}" title="\${name}">
		</div>
`;

var jstpl_winDialogTemplate = `
		<div class="flx-win-dialog-content">
      <div id="flx-win-dialog-msg" ><h3>\${msg}</h3></div>
      <div id="flx-win-dialog-goal"></div>
		</div>
`;

</script>

{OVERALL_GAME_FOOTER}
