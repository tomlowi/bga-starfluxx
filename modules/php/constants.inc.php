<?php

define("RULE_PLAY_RULE", 0);
define("RULE_DRAW_RULE", 1);
define("RULE_KEEPERS_LIMIT", 2);
define("RULE_HAND_LIMIT", 3);
define("RULE_OTHERS", 4);

define("PLAY_COUNT_ALL", 999); // 104 cards total in play, so all

// in the Surprises queue, we need location_arg to keep an ordered queue, but still be able to determine the player from it
// HACK here because deck component uses location_arg differently for
// "hand" style locations and "pile" style locations:
// here we want a bit of both, so we add player_id to a large enough offset that we can use for ordering,
// while still being able to extract back the owner player_id from it (with modulo)
define("OFFSET_PLAYER_LOCATION_ARG", 100000000000);

?>
