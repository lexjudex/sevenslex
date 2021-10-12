{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- sevenslex implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    sevenslex_sevenslex.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="spadeArea" class="whiteblock">
	<div class="cardontable" id="cardontable_7spade" style="background-position:-360px -0px; left: 512px;">
						</div>
</div>
<div id="heartArea" class="whiteblock">
	<div class="cardontable" id="cardontable_7heart" style="background-position:-360px -96px; left: 512px;">
						</div>
</div>
<div id="clubArea" class="whiteblock">
	<div class="cardontable" id="cardontable_7club" style="background-position:-360px -192px; left: 512px;">
						</div>
</div>
<div id="diamondArea" class="whiteblock">
	<div class="cardontable" id="cardontable_7diamond" style="background-position:-360px -288px; left: 512px;">
						</div>
</div>

<div id="playertables">

    <!-- BEGIN player -->
    <div class="playertable whiteblock playertable_{DIR}">
        <div class="playertablename" style="color:#{PLAYER_COLOR}">
            {PLAYER_NAME}
			<h3>Cards: <span id="cardsremaining_{PLAYER_ID}"></span></h3>
			<h3>Passes: <span id="passremaining_{PLAYER_ID}"></span></h3>
        </div>
			<div class="playertablecard" id="playertablecard_{PLAYER_ID}">
        </div>
    </div>
    <!-- END player -->

</div>

<div id="myhand_wrap" class="whiteblock" style="top: 200px;">
    <h3>{MY_HAND}</h3>
    <div id="myhand">
    </div>
</div>


<script type="text/javascript">

// Javascript HTML templates

//var jstpl_cardontable = '<div class="cardontable" id="cardontable_${player_id}" style="background-position:-${x}px -${y}px">\
                        //</div>';
						
var jstpl_cardonsuit = '<div class="cardontable" id="cardontable_${player_id}_${card_id}" style="background-position:-${x}px -${y}px; left: ${pos}px; top: ${ofst}px;">\
						</div>';
						
var jstpl_cardcounter = '<span id="cardcount_${id}">0</span>';

var jstpl_passcounter = '<span id="passcount_${id}">0</span>';
						


/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

</script>  

{OVERALL_GAME_FOOTER}
