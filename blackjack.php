<?php

// create an array for suits
$suits = ['C', 'H', 'S', 'D'];

// create an array for values
$values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

// group card elements into one array
$cardElements = [
    'value' => $values,
    'suit' => $suits
];

// build a deck (array) of cards
// cards should be "VALUE SUIT". ex: "7 H"
// make sure to shuffle the deck before returning it
function buildDeck($input) {
    // filter out any potentially empty values (does not apply here)
    $input = array_filter($input);
    // set result to an array within an array
    $result = [array()];
    // get cartesian product of the associative cardElements array
    foreach ($input as $key => $items) {
        $append = array();

        foreach($result as $product) {
            foreach($items as $item) {
                $product[$key] = $item;
                $append[] = $product;
            }
        }
        // append the arrays to the result array 
        $result = $append;
    }
    // shuffle the deck and return it
    shuffle($result);
    return $result;
}  

// determine if a card is an ace
// return true for ace, false for anything else
function cardIsAce($card) {
    if ($card['value'] == 'A') {
        return true;
    } else {
        return false;
    }
}

// determine if card is a face card
// return true for face card, false for anything else
function faceCard($card) {
    if ($card['value'] == 'J' || $card['value'] == 'Q' || $card['value'] == 'K') {
        return true;
    } else {
        return false;
    }
}

// determine the value of an individual card (string)
// aces are worth 11
// face cards are worth 10
// numeric cards are worth their value
function getCardValue($card) {
    if (cardIsAce($card)) {
        return 11;
    } elseif (faceCard($card)) {
        return 10;
    } else {
        return intval($card['value']);
    }
}

// get total value for a hand of cards
// don't forget to factor in aces
// aces can be 1 or 11 (make them 1 if total value is over 21)
function getHandTotal($hand) {
    $total = 0;

    foreach($hand as $card) {
        $total += getCardValue($card);

        if (cardIsAce($card) && $total > 21) {
            $total -= 10;
        }
    }

    return $total;
}

// draw a card from the deck into a hand
// pass by reference (both hand and deck passed in are modified)
function drawCard(&$hand, &$deck) {
    $hand[] = array_pop($deck);
}

// print out a hand of cards
// name is the name of the player
// hidden is to initially show only first card of hand (for dealer)
// output should look like this:
// Dealer: [4 C] [???] Total: ???
// or:
// Player: [J D] [2 D] Total: 12
function echoHand($hand, $name, $hidden = false) {
    if ($hidden) {
        // show only dealer's first card only
        foreach($hand as $key => $card) {
            if ($key == 0) {
                echo "\n$name: [" . $hand[0]['value'] . " of " . $hand[0]['suit'] . "] ";
            } else {
                echo "[??????] Total: ??" . PHP_EOL;
            }
        }

    } else {
        // show all of player's cards or dealer's cards
        echo "\n-----------------------------------------------";

        foreach($hand as $key => $card) {
            if ($key == 0) {
                echo "\n$name: [" . $hand[0]['value'] . " of " . $hand[0]['suit'] . "] ";
            } elseif ($key == count($hand) - 1) {
                echo "[" . $hand[$key]['value'] . " of " . $hand[$key]['suit'] . "] Total: " . getHandTotal($hand) . PHP_EOL . PHP_EOL;
            } else {
                echo "[" . $hand[$key]['value'] . " of " . $hand[$key]['suit'] . "] ";
            }
        }
    }
}


// show game title
echo "\n===============================================\n";
echo "++++++21++++++ B L A C K J A C K ++++++21++++++\n";
echo "===============================================\n";

echo "\nHowdy! Enter your name to begin: ";
$playerName = strtoupper(trim(fgets(STDIN)));


do {
    // build the deck of cards
    $deck = buildDeck($cardElements);

    // initialize a dealer and player hand
    $dealer = [];
    $player = [];

    // set $busted and $playerWon to false by default
    $busted = false;
    $playerWon = false;

    // dealer and player each draw two cards
    drawCard($dealer, $deck);
    drawCard($player, $deck);
    drawCard($dealer, $deck);
    drawCard($player, $deck);

    echo "\n-----------------------------------------------";

    // echo the dealer hand, only showing the first card
    echoHand($dealer, 'Dealer', true);

    // echo the player hand
    echoHand($player, $playerName);

    // allow player to "(H)it or (S)tay?" till they bust (exceed 21) or stay
    while (getHandTotal($player) <= 21) {

        // if player's total is 21 tell them they won (regardless of dealer hand)
        if (getHandTotal($player) == 21) {
            $busted = false;  
            $playerWon = true;   
        }

        if ($playerWon != true) {
            echo "(H)it or (S)tay? ";
            $input = strtoupper(trim(fgets(STDIN)));
        }

        if ($input == 'H' && $playerWon != true) {
            drawCard($player, $deck);
            echoHand($player, $playerName);
        } else {
            break;
        }
    }

    // show the dealer's hand (all cards)
    echoHand($dealer, 'Dealer');

    // at this point, if the player has more than 21, tell them they busted
    if (getHandTotal($player) > 21) {
        echo "\n>> You busted. Dealer won!\n";
        $busted = true;
    } elseif (getHandTotal($player) == 21) {
    // otherwise, if they have 21, tell them they won (regardless of dealer hand)
        $busted = false;
    } else {
    // if neither of the above are true, then the dealer needs to draw more cards
    // dealer draws until their hand has a value of at least 17
        while (getHandTotal($dealer) <= 17) {
           drawCard($dealer, $deck); 
           // show the dealer hand each time they draw a card
           echoHand($dealer, 'Dealer');
       }
    }

    // finally, we can check and see who won
    if (getHandTotal($dealer) > 21) {
        // by this point, if dealer has busted, then player automatically wins
        echo "\n>> Dealer busted. You won!\n";
    } elseif (getHandTotal($dealer) == getHandTotal($player)) {
        // if player and dealer tie, it is a "push"
        echo "\n>> Standoff (tied!)\n";
    } elseif(getHandTotal($dealer) > getHandTotal($player)) {
    // if dealer has more than player, dealer wins, otherwise, player wins
        echo "\n>> Dealer won!\n";
    } elseif(getHandTotal($dealer) < getHandTotal($player) && $busted == false) {
        echo "\n>> You won!\n";
    }

    // ask user if they want to play again
    echo "\n>> PLAY AGAIN? (Y)es or (N)o: ";
    $playAgain = strtoupper(trim(fgets(STDIN)));

    if ($playAgain == 'Y') {
        $rematch = true;
    } else {
        $rematch = false;
    }

} while ($rematch != false);

