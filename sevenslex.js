/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * sevenslex implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * sevenslex.js
 *
 * sevenslex user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
	"ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.sevenslex", ebg.core.gamegui, {
        constructor: function(){
            console.log('sevenslex constructor');
			this.cardwidth = 72;
            this.cardheight = 96;
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            
			this.card_counters = {};
			this.pass_counters = {};
			
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                // TODO: Setting up players boards if needed
				var card_count_div = $('cardsremaining_'+player_id);
				dojo.place( this.format_block('jstpl_cardcounter', player ), card_count_div );
				
				var pass_count_div = $('passremaining_'+player_id);
				dojo.place( this.format_block('jstpl_passcounter', player ), pass_count_div );
				
				//Setup Counters
				this.card_counters[player_id] = new ebg.counter();
				this.card_counters[player_id].create('cardcount_'+player_id);
				this.card_counters[player_id].setValue(player.cards);
				
				this.pass_counters[player_id] = new ebg.counter();
				this.pass_counters[player_id].create('passcount_'+player_id);
				this.pass_counters[player_id].setValue(player.passes);
            }
            
            // TODO: Set up your game interface here, according to "gamedatas"
			// Player hand
			this.playerHand = new ebg.stock(); // new stock object for hand
			this.playerHand.create( this, $('myhand'), this.cardwidth, this.cardheight );
            
			this.playerHand.image_items_per_row = 13; // 13 images per row
			
			dojo.connect( this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' ); 


            // Create cards types:
            for (var color = 1; color <= 4; color++) {
                for (var value = 2; value <= 14; value++) {
                    // Build card type id
                    var card_type_id = this.getCardUniqueId(color, value);
                    
					if (value == 14) {
						this.playerHand.addItemType(card_type_id, card_type_id - 13, g_gamethemeurl + 'img/cards.jpg', card_type_id);
					} else {
						this.playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.jpg', card_type_id);
					}
                }
            }
			
			// Cards in player's hand
            for ( var i in this.gamedatas.hand) {
                var card = this.gamedatas.hand[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }
			



			var cardsPlayed = new Array();
			
            // Cards played on table
            for (i in this.gamedatas.cardsontable) {
                var card = this.gamedatas.cardsontable[i]; 
				cardsPlayed[i] = card;
                var color = card.type;
                var value = card.type_arg;
                var player_id = card.location_arg;
				var offset = 12;
				if (value == 7) {
					offset = 30;
				}
				for (j in cardsPlayed) {
					if ( cardsPlayed[j].type == color && cardsPlayed[j].type_arg == value && cardsPlayed[j].id != card.id) {
						offset = 30;
					}
				}
                this.playCardOnTable(player_id, color, value, card.id, offset);
            }
			

			
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {     

				this.addActionButton( 'passbutton', _('Pass'), 'pass' );
				
                switch( stateName )
                {
/*               

                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
		
		 // Get card unique identifier based on its color and value
        getCardUniqueId : function(color, value) {
            return (color - 1) * 13 + (value - 2);
        },
		
		playCardOnTable : function(player_id, color, value, card_id, offset) {
			
			var suit = '';
			if(color == 1) {
				suit = 'spadeArea';
			} else if(color == 2) {
				suit = 'heartArea';
			} else if(color == 3) {
				suit = 'clubArea';
			} else {
				suit = 'diamondArea';
			}
			
			var position;
			
			if (value == 14) {
				position = 2;
			} else {
				position = (value-1) * 85 + 2;
			}
			
			dojo.place(this.format_block('jstpl_cardonsuit', {
                x : this.cardwidth * (value - 2),
                y : this.cardheight * (color - 1),
				pos : position,
				ofst : offset,
                player_id : player_id,
				card_id : card_id
            }), suit);

            if (player_id != this.player_id) {
                // Some opponent played a card
                // Move card from player panel
                this.placeOnObject('cardontable_' + player_id + '_' + card_id, 'overall_player_board_' + player_id);
            } else {
                // You played a card. If it exists in your hand, move card from there and remove
                // corresponding item

                if ($('myhand_item_' + card_id)) {
                    this.placeOnObject('cardontable_' + player_id + '_' + card_id, 'myhand_item_' + card_id);
                    this.playerHand.removeFromStockById(card_id);
					//this.card_counters[player_id].incValue(-1);
                }
            }

            // In any case: move it to its final destination
			
			
            this.slideToObjectPos('cardontable_' + player_id + '_' + card_id, suit, position, offset).play();
        },


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
		
		onPlayerHandSelectionChanged : function() {
            var items = this.playerHand.getSelectedItems();

            if (items.length > 0) {
                var action = 'playCard';
                if (this.checkAction(action, true)) {
                    // Can play a card
                    var card_id = items[0].id;
                    this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", {
                        id : card_id,
                        lock : true
                    }, this, function(result) {
                    }, function(is_error) {
                    });

                    this.playerHand.unselectAll();
                } else if (this.checkAction('giveCards')) {
                    // Can give cards => let the player select some cards
                } else {
                    this.playerHand.unselectAll();
                }
            }
        },
        
		pass : function() {
			if (this.checkAction('pass')) {
				this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + "pass" + ".html", {
							lock : true
						}, this, function(result) {
						}, function(is_error) {
						});
			}
		},
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/sevenslex/sevenslex/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your sevenslex.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
			dojo.subscribe('newHand', this, "notif_newHand");
            dojo.subscribe('playCard', this, "notif_playCard");
			dojo.subscribe('pass', this, "notif_pass");
			dojo.subscribe( 'newScores', this, "notif_newScores" );
			dojo.subscribe('usedPass', this, "notif_usedPass" );
			dojo.subscribe('usedCard', this, "notif_usedCard" );
			//Add notif handler for win TODO
			//dojo.subscribe( 'trickWin', this, "notif_trickWin" );
            //this.notifqueue.setSynchronous( 'trickWin', 1000 );
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
		notif_newHand : function(notif) {
            // We received a new full hand of cards.
            this.playerHand.removeAll();

            for ( var i in notif.args.cards) {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }
        },
		
		notif_newScores : function(notif) {
           // Update players' scores
           for ( var player_id in notif.args.newScores) {
               this.scoreCtrl[player_id].toValue(notif.args.newScores[player_id]);
           }
		},
		
		notif_usedPass : function(notif) {
			// Update players' pass counter
           for ( var player_id in notif.args.usedPass) {
               this.pass_counters[player_id].toValue(notif.args.usedPass[player_id]);
           }
		},
		
		notif_usedCard : function(notif) {
			// Update players' pass counter
           for ( var player_id in notif.args.usedCard) {
               this.card_counters[player_id].toValue(notif.args.usedCard[player_id]);
           }
		},

        notif_playCard : function(notif) {
            // Play a card on the table
            this.playCardOnTable(notif.args.player_id, notif.args.color, notif.args.value, notif.args.card_id, notif.args.offset);
			//this.card_counters[notif.args.player_id].incValue(-1);
        },
		
		notif_pass : function(notif) {
			// Decrement their pass counter
			//this.pass_counters[notif.args.player_id].toValue(notif.args.pass_left);
		}
		
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
