<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * sevenslex implementation : © <Your name here> <Your email address here>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * sevenslex.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class sevenslex extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
		self::initGameStateLabels( array( 
		//GameStateLabels for Hearts, Need to update for sevenslex
                         //"currentHandType" => 10, 
                         //"trickColor" => 11, 
                         //"alreadyPlayedHearts" => 12,
						 "winnerPoints" => 10,
						 "loserPoints" => 11,
						 "playersLeft" => 12,
						 "variation" => 101
                          ) );

        $this->cards = self::getNew( "module.common.deck" );
        $this->cards->init( "card" );
             
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "sevenslex";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
		$playerCount = 0;
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
			$playerCount += 1;
		}
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)
		
		self::initStat( 'player', 'turns_number', 0);
		self::initStat( 'player', 'pass_left', 3);

        // TODO: setup the initial game situation here
		
		// Init global values with their initial values
		//Hearts Globals commented out.

        // Note: hand types: 0 = give 3 cards to player on the left
        //                   1 = give 3 cards to player on the right
        //                   2 = give 3 cards to player opposite
        //                   3 = keep cards
        //self::setGameStateInitialValue( 'currentHandType', 0 );
        
        // Set current trick color to zero (= no trick color)
        //self::setGameStateInitialValue( 'trickColor', 0 );
        
        // Mark if we already played hearts during this hand
        //self::setGameStateInitialValue( 'alreadyPlayedHearts', 0 );
		
		$totalPlayers = self::getPlayersNumber();
		self::setGameStateInitialValue( 'winnerPoints', 0 );
		
		self::setGameStateInitialValue( 'loserPoints', 1 );
		
		self::setGameStateInitialValue( 'playersLeft', $playerCount );
		
		// Create cards
        $cards = array ();
		
		if (self::isVariant()) {
			foreach ( $this->colors as $color_id => $color ) {
				// spade, heart, diamond, club
				for ($value = 2; $value <= 14; $value ++) {
					//  2, 3, 4, ... K, A
					if ($value != 7) {
						$cards [] = array ('type' => $color_id,'type_arg' => $value,'nbr' => 2 );
					} else {
						$cards [] = array ('type' => $color_id,'type_arg' => $value,'nbr' => 1 );
					}
				}
			}
		} else {
			foreach ( $this->colors as $color_id => $color ) {
				// spade, heart, diamond, club
				for ($value = 2; $value <= 14; $value ++) {
					//  2, 3, 4, ... K, A
					if ($value != 7) {
						$cards [] = array ('type' => $color_id,'type_arg' => $value,'nbr' => 1 );
					}
				}
			}
		}
        
        $this->cards->createCards( $cards, 'deck' );
		
		/*
		// Shuffle deck
        $this->cards->shuffle('deck');
		
		
        // Deal 13 cards to each players
        $players = self::loadPlayersBasicInfos();
		for ( $i = 0; $i < 52;) {
			foreach ( $players as $player_id => $player ) {
				$cards = $this->cards->pickCards(1, 'deck', $player_id);
				$i++;
			} 
		}
		*/
       

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, card_count cards, pass_count passes FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
		
		// Cards in player hand
        $result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );
        
        // Cards played on the table
        $result['cardsontable'] = $this->cards->getCardsInLocation( 'cardsontable' );
		
		$result['currentID'] = $current_player_id;
		
		$result['isVariant'] = self::isVariant();
		
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {

        return $this->cards->countCardInLocation('cardsontable');
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */
	
		// get score
	   function dbGetScore($player_id) {
		   return $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='$player_id'");
	   }
		
		// set score
	   function dbSetScore($player_id, $count) {
		   $this->DbQuery("UPDATE player SET player_score='$count' WHERE player_id='$player_id'");
	   }
	   
		// increment score (can be negative too)
	   function dbIncScore($player_id, $inc) {
		   $count = $this->dbGetScore($player_id);
		   if ($inc != 0) {
			   $count += $inc;
			   $this->dbSetScore($player_id, $count);
		   }
		   return $count;
	   }
	 
	   // get counter
	   function dbGetCards($player_id) {
		   return $this->getUniqueValueFromDB("SELECT card_count FROM player WHERE player_id='$player_id'");
	   }
		
		// set counter
	   function dbSetCards($player_id, $count) {
		   $this->DbQuery("UPDATE player SET card_count='$count' WHERE player_id='$player_id'");
	   }
	   
		// increment counter (can be negative too)
	   function dbIncCards($player_id, $inc) {
		   $count = $this->dbGetCards($player_id);
		   if ($inc != 0) {
			   $count += $inc;
			   $this->dbSetCards($player_id, $count);
		   }
		   return $count;
	   } 
	   
	   // get counter
	   function dbGetPass($player_id) {
		   return $this->getUniqueValueFromDB("SELECT pass_count FROM player WHERE player_id='$player_id'");
	   }
		
		// set counter
	   function dbSetPass($player_id, $count) {
		   $this->DbQuery("UPDATE player SET pass_count='$count' WHERE player_id='$player_id'");
	   }
	   
		// increment counter (can be negative too)
	   function dbIncPass($player_id, $inc) {
		   $count = $this->dbGetPass($player_id);
		   if ($inc != 0) {
			   $count += $inc;
			   $this->dbSetPass($player_id, $count);
		   }
		   return $count;
	   } 
	   
	   public function isVariant() {
		return $this->getGameStateValue('variation') == 1;
		}
		
		public function findValue($suit, $value) {
			$tableCards = $this->cards->getCardsInLocation( 'cardsontable' );

			foreach ( $tableCards as $card ) {
					//Check if same suit
					if ($suit == $card['type']) {
						//Check if card number exists
						if ($card['type_arg'] == $value) {
							return true;
						}
					}
			}
			return false;
		}


//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in sevenslex.action.php)
    */
	
	function playCard($card_id) {
        self::checkAction("playCard");
        $player_id = self::getActivePlayerId();
		self::incStat( 1, "turns_number", $player_id);
		
		// XXX check rules here
		//Get card information
		$currentCard = $this->cards->getCard($card_id);
		
		//Get cards played
		$tableCards = $this->cards->getCardsInLocation( 'cardsontable' );
		
		
		$offset = 12;
		// Check to see if 2 version of card to be played
		foreach ( $tableCards as $card ) {
					//Check if same suit
					if ($currentCard['type'] == $card['type']) {
						//Check if card comes between seven and current card
						if ($card['type_arg'] == $currentCard['type_arg'] && $card['id'] != $currentCard['id']) {
							$offset = 30;
						}
					}
				}
		
		
		
		// Check if 7, 7 is always playable
		if ($currentCard['type_arg'] != 7) {
			
			$playable = false;
			
			// Check if current card is above or below seven
			if ($currentCard['type_arg'] > 7 && $currentCard['type_arg'] != 14) {
				$cardsBetween = 0;
				foreach ( $tableCards as $card ) {
					//Check if same suit
					if ($currentCard['type'] == $card['type']) {
						//Check if card comes between seven and current card
						if ($card['type_arg'] >= 7 && $card['type_arg'] < $currentCard['type_arg']) {
							$cardsBetween++;
						}
					}
				}
				if ($offset == 30) {
					$playable = ($cardsBetween == ($currentCard['type_arg'] - 7) * 2 - 1);
				} else {
					$playable = true;
					for ( $i = $currentCard['type_arg'] - 1; $i > 7; $i--) {
						if(!self::findValue($currentCard['type'], $i)) {
							$playable = false;
						}
					}
				}
			} else {
				$currentNum = $currentCard['type_arg'];
				if ($currentNum == 14)
					$currentNum = 1;
				$cardsBetween = 0;
				foreach ( $tableCards as $card ) {
					//Check if same suit
					if ($currentCard['type'] == $card['type']) {
						//Check if card comes between seven and current card
						if ($card['type_arg'] <= 7 && $card['type_arg'] > $currentNum) {
							$cardsBetween++;
						}
					}
				}
				if ($offset == 30) {
					$playable = ($cardsBetween == (7 - $currentNum) * 2 - 1);
				} else {
					$playable = true;
					for ( $i = $currentNum + 1; $i < 7; $i++) {
						if(!self::findValue($currentCard['type'], $i)) {
							$playable = false;
						}
					}
				}
			}
			
			// If card is not playable, throw exception
			if(!$playable) {
				throw new feException( self::_("You must play a card 1 above or below a played card"), true );
			}
		} else {
			$offset = 30;
		}
		
		
		//If card is playable, play card
        $this->cards->moveCard($card_id, 'cardsontable', $player_id);
		self::dbIncCards($player_id, -1);
        // And notify
        self::notifyAllPlayers('playCard', clienttranslate('${player_name} plays ${value_displayed} ${color_displayed}'), array (
                'i18n' => array ('color_displayed','value_displayed' ),'card_id' => $card_id,'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),'value' => $currentCard ['type_arg'],
                'value_displayed' => $this->values_label [$currentCard ['type_arg']],'color' => $currentCard ['type'],
                'color_displayed' => $this->colors [$currentCard ['type']] ['name'], 'offset' => $offset));
		
		
				
		//Check if active player is out of cards
		$players = self::loadPlayersBasicInfos();
		$current_player_id = self::getActivePlayerId();
		if ($this->cards->countCardInLocation( 'hand', $current_player_id ) == 0 ) {
			
			$score = self::getPlayersNumber() - self::getGameStateValue("winnerPoints");
			
			self::dbIncScore($current_player_id, $score);
			self::incGameStateValue( 'winnerPoints', 1 );
			self::incGameStateValue( 'playersLeft', -1 );
			
			self::notifyAllPlayers("points", clienttranslate('${player_name} went out and gets ${nbr} points!'), array (
                        'player_id' => $current_player_id,'player_name' => $players [$current_player_id] ['player_name'],
                        'nbr' => $score ));
			
			$newScores = self::getCollectionFromDb("SELECT player_id, player_score FROM player", true );
			self::notifyAllPlayers( "newScores", '', array( 'newScores' => $newScores ) );
			
		}
		
		
        // Next player
        $this->gamestate->nextState('playCard');
    }
	
	function pass() {
		
		self::checkAction("pass");
		$player_id = self::getActivePlayerId();
		self::dbIncPass($player_id, -1);
		self::incStat( -1, "pass_left", $player_id);
		self::incStat( 1, "turns_number", $player_id);
		$pass_left = self::dbGetPass($player_id);
		// And notify
		if ($pass_left >= 0 ) {
			self::notifyAllPlayers('pass', clienttranslate('${player_name} passed. They have ${pass_left} passes left.'), array (
					'player_id' => $player_id,
					'pass_left' => $pass_left,
					'player_name' => self::getActivePlayerName()));
			
			
		} else {
			// Get the cards remaining in the player's hand
			$playerhands = $this->cards->getCardsInLocation( 'hand', $player_id );
			
			foreach ( $playerhands as $card ) {
				
				
				$tableCards = $this->cards->getCardsInLocation( 'cardsontable' );
				$offset = 12;
				// Check to see if 2nd version of card to be played
				foreach ( $tableCards as $card_2 ) {
							//Check if same suit
							if ($card['type'] == $card_2['type']) {
								if ($card_2['type_arg'] == $card['type_arg']) {
									$offset = 30;
								}
							}
						}
				if ( $card['type_arg'] == 7 ) {
					$offset = 30;
				}
				
				$this->cards->moveCard($card['id'], 'cardsontable', $player_id);
				self::dbIncCards($player_id, -1);
				
				// And notify
				self::notifyAllPlayers('playCard', clienttranslate('${player_name} plays ${value_displayed} ${color_displayed}'), array (
                'i18n' => array ('color_displayed','value_displayed' ),'card_id' => $card['id'],'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),'value' => $card ['type_arg'],
                'value_displayed' => $this->values_label [$card ['type_arg']],'color' => $card ['type'],
                'color_displayed' => $this->colors [$card ['type']] ['name'], 'offset' => $offset ));
			}
			
			self::notifyAllPlayers('pass', clienttranslate('${player_name} passed. They have no passes left. All of their remaining cards have been played.'), array (
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName()));
			
			$players = self::loadPlayersBasicInfos();
			$score = self::getGameStateValue("loserPoints");
			
			self::dbIncScore($player_id, $score);
			self::incGameStateValue( 'loserPoints', 1 );
			self::incGameStateValue( 'playersLeft', -1 );
			
			self::notifyAllPlayers("points", clienttranslate('${player_name} went out and gets ${nbr} points!'), array (
                        'player_id' => $player_id,'player_name' => $players [$player_id] ['player_name'],
                        'nbr' => $score ));
			
			$newScores = self::getCollectionFromDb("SELECT player_id, player_score FROM player", true );
			self::notifyAllPlayers( "newScores", '', array( 'newScores' => $newScores ) );
			
			
			
		}
		// Next Player
		$this->gamestate->nextState('pass');
		
	}

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */
	
	function argGiveCards() {
        return array ();
    }

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
	
	function stNewHand() {
        // Take back all cards (from any location => null) to deck
        $this->cards->moveAllCardsInLocation(null, "deck");
		
		$players = self::loadPlayersBasicInfos();
		
        $this->cards->shuffle('deck');
		
		// Set counters to default
		foreach ( $players as $player_id => $player ) {
			self::dbSetPass($player_id, 3, "pass_count");
			self::dbSetCards($player_id, 0, "card_count");
		}
		
		//Update Counters for players
		
		$usedPass = self::getCollectionFromDb("SELECT player_id, pass_count FROM player", true );
		self::notifyAllPlayers( "usedPass", '', array( 'usedPass' => $usedPass ) );
		
        // Deal  cards to each players
		
        // Create deck, shuffle it and give cards
		if (self::isVariant() ) {
			for ( $i = 0; $i < 100;) {
				foreach ( $players as $player_id => $player ) {
					$cards = $this->cards->pickCards(1, 'deck', $player_id);
					$i++;
				// Notify player about his cards
				self::notifyPlayer($player_id, 'newHand', '', array ('cards' => $cards ));
				} 
			}
		} else {
			for ( $i = 0; $i < 48;) {
				foreach ( $players as $player_id => $player ) {
					$cards = $this->cards->pickCards(1, 'deck', $player_id);
					$i++;
				// Notify player about his cards
				self::notifyPlayer($player_id, 'newHand', '', array ('cards' => $cards ));
				} 
			}
		}
		
		foreach ( $players as $player_id => $player ) {
			self::dbSetCards($player_id, $this->cards->countCardInLocation( 'hand', $player_id ));
		}
		
		$usedCard = self::getCollectionFromDb("SELECT player_id, card_count FROM player", true );
		self::notifyAllPlayers( "usedCard", '', array( 'usedCard' => $usedCard ) );
		
        $this->gamestate->nextState("");
    }

    function stNextPlayer() {
		
		//Update Counters for players
		
		$usedPass = self::getCollectionFromDb("SELECT player_id, pass_count FROM player", true );
		self::notifyAllPlayers( "usedPass", '', array( 'usedPass' => $usedPass ) );
		
		$usedCard = self::getCollectionFromDb("SELECT player_id, card_count FROM player", true );
		self::notifyAllPlayers( "usedCard", '', array( 'usedCard' => $usedCard ) );
		
		
		
        // Active next player OR end the hand
        if ($this->cards->countCardInLocation('cardsontable') == 100 || self::getGameStateValue( 'playersLeft' ) == 1) {
            // This is the end of the hand
			// Notify
            // Note: we use 2 notifications here in order we can pause the display during the first notification
            //  before we move all cards to the winner (during the second)
            $players = self::loadPlayersBasicInfos();
			
			$player_id = self::activeNextPlayer();
			while ($this->cards->countCardInLocation('hand' , $player_id) == 0) {
				$player_id = self::activeNextPlayer();
			}
			
			$score = self::getPlayersNumber() - self::getGameStateValue("winnerPoints");
			
			self::dbIncScore($player_id, $score);
			self::incGameStateValue( 'winnerPoints', 1 );
			
			self::notifyAllPlayers("points", clienttranslate('${player_name} went out and gets ${nbr} points!'), array (
                        'player_id' => $player_id,'player_name' => $players [$player_id] ['player_name'],
                        'nbr' => $score ));
			
			$newScores = self::getCollectionFromDb("SELECT player_id, player_score FROM player", true );
			self::notifyAllPlayers( "newScores", '', array( 'newScores' => $newScores ) );
			
			
			
			//Notify of winning player TODO
             $this->gamestate->nextState("endHand");
        } else {
            // Standard case (not the end of the hand)
            // => just active the next player
			$player_id = self::activeNextPlayer();
			while ($this->cards->countCardInLocation('hand' , $player_id) == 0) {
				$player_id = self::activeNextPlayer();
			}
			self::giveExtraTime($player_id);
			$this->gamestate->nextState('nextPlayer');
        }
    }

    function stEndHand() {
		
		$newScores = self::getCollectionFromDb("SELECT player_id, player_score FROM player", true );
		self::notifyAllPlayers( "newScores", '', array( 'newScores' => $newScores ) );

        ///// Test if this is the end of the game
        foreach ( $newScores as $player_id => $score ) {
			// Check if score threshold is met. Can be changed to any number
            if ($score >= 1) {
                // Trigger the end of the game !
                $this->gamestate->nextState("endGame");
                return;
            }
        }
		
        $this->gamestate->nextState("nextHand");
    }
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    self::pass();
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
