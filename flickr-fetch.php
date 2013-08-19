<?php
/*
Five Card Flickr Site
by Alan Levine, cogdogblog@gmail.com
inspired completely by Five-Card Nancy http://www.7415comics.com/nancy/

Five Card Flickr is Copyright  Alan Levine and is distributed under the terms of the GNU General Public License Code Available from http://www.gnu.org/licenses/gpl.html

Code available from https://github.com/cogdog/5cardflickr



flickr-fetch.php
Call this script  to fetch new photos from flickr only to brun from a cron script.

It is the site owners responsibility to not overload the flickr api; a call once an hour
is recommended

You can rename this file if you prefer to keep it a secret.

For crontab, use something like to run every hour at the 15 minute mark:

15 * * * * curl http://www.yoursite.com/5cardflickr/flickr-fetch.php

	
*/  



// ------- CONFIG --------------------------------------------------------------

require_once('utils.php');   	// common code library
require_once('config.php'); 	// configuration files for entire site

// get an array of the tags we need to fetch for
$the_tags = get_all_tags($decks);

foreach($the_tags as $tag) {
	
	// get the "suit"- the array label for this tag
	$suit = get_suit_from_tag($decks,$tag,$default_deck);
	
	// Get the photos
	
	$start_time = time();
	$new_photos = get_from_flickr($db, CARD_DECK, $tag, $verbose, $maxdate);
	
	// output
	echo "At " . date('r') . " $new_photos photos added to the cards for tag '$tag'. Execution time= " . (time() - $start_time)  . " seconds.\n";
}

// update summary stats for site
$story_count = get_tbl_count($db, 'stories');
$photocount = get_tbl_count($db, 'cards');

$output =  "As of " . date('r') . " <strong>$story_count</strong> <a href=\"show.php\">Five Card stories</a> have been created on this site from a pool of <strong>$photocount</strong> tagged photos.";

// update the file with total
$DATAFILE = fopen('5cardstats.inc',"w+");
fwrite($DATAFILE,$output) or die(" failed writing $DATAFILE");
fclose($DATAFILE);


?>
