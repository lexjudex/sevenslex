<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Sevenslex implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * Sevenslex game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in sevenslex.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    /*
    
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('my game option'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name' => totranslate('option 1') )

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name' => totranslate('option 2'), 'tmdisplay' => totranslate('option 2') ),

                            // Another value, with other options:
                            //  description => this text will be displayed underneath the option when this value is selected to explain what it does
                            //  beta=true => this option is in beta version right now.
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            3 => array( 'name' => totranslate('option 3'), 'description' => totranslate('this option does X'), 'beta' => true, 'nobeginner' => true )
                        ),
                'default' => 1
            ),

    */
	/*100 => array(
         'name' => totranslate('Base Game'),
         'values' => array(
             // A simple value for this option:
			 1 => array( 'name' => totranslate('Off'), 'tmdisplay' => totranslate('') ),
			 2 => array( 'name' => totranslate('On'), 'tmdisplay' => totranslate('1 Deck') ),
         ),
		 'startcondition' => array(
             1 => array(
                 array(
					'type' => 'minplayers',
					'value' => 4,
					'message' => totranslate('1 Deck requires 4 players.')
				),
             ),
             2 => array(
                 array(
					'type' => 'maxplayers',
					'value' => 5,
					'message' => totranslate('1 Deck requires 5 players.')
				),
             ),
         ),
     ),
	 */
	101 => array(
         'name' => totranslate(' 1 Deck '),
         'values' => array(
             // A simple value for this option:
			 1 => array( 'name' => totranslate('Off'), 'tmdisplay' => totranslate('2 Deck Variant')),
             2 => array( 'name' => totranslate('On'), 'tmdisplay' => totranslate('1 Deck') ),
         ),
		 
		 'startcondition' => array(
			1 => array(
				 array(
					'type' => 'minplayers',
					'value' => 4,
					'message' => totranslate('Requires at least 4 players.')
				),
			),

			2 => array(
                 array(
					'type' => 'maxplayers',
					'value' => 5,
					'message' => totranslate('Requires at most 5 players.')
				),
			),
			),
         ),
);


