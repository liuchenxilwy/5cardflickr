<?php
/*
Five Card Flickr 
version 3.0 (Aug 18, 2013)

Site http://5card.cogdogblog.com
by Alan Levine, cogdogblog@gmail.com
inspired completely by Five-Card Nancy http://www.7415comics.com/nancy/

Five Card Flickr is Copyright 2008-2011 Alan Levine and is distributed under the terms of the GNU General Public License 

Code available from https://github.com/cogdog/5cardflickr

*/ 


// ------- CONFIGURATION -------------------------------------------------------


// database configuration
define('MYSQL_HOST','');
define('MYSQL_USER','');
define('MYSQL_PASS','');

// name of the database
$dbname = '';

// email to notify of failed database connections; set to blank to skip emailing
define('CHIEF','5Card Admin <you@gmail.com>');

// flickr api key. Get one at http://www.flickr.com/services/apps/create/apply/
define('FLICKRKEY','');

// google translation meta key - leave blank to disable feature
// get one at https://translate.google.com/manager/website/settings
define('GTRANSKEY', '');

// database table with all photo information
define('CARD_DECK', 'cards'); 

// number of stories to show per page on gallery
define('PAGER', 200);

// number of photos to show per page on tag listing
define('PHOTOPAGER', 160);

// password for access to admin tool
define('ADMINKEY', '*********');

// define site overall title (displayed in header)
define('SITENAME', 'Five Card Flickr Stories');

// connect to database
$db = db_connect($dbname);


/* 	define your card decks- you will need one array item for each different
	implemntation (e.g. for each tag you are going to serve on this site.
	You must have at least one defined! The array keys can be any string of your
	choosing.
	
	An array contains the following:
		* title= display name
		* tag = flickr tage used for images
*/

$decks = array(
			'5card' => array (
					'title' => 'Five Card Flickr',
					'tag' => '5cardflickr',
					),
		);

$default_deck = '5card';

$use_captcha = true; // flag to use captcha for user submissions, highly recommended

if ($use_captcha) {	
	// Get a key from http://recaptcha.net/api/getkey
	$publickey = "<insert public key>";
	$privatekey = "<insert private key>";
}

// for debugging purposes only
//ini_set('error_reporting', E_ALL^ E_NOTICE);


// - no configuration below here, just some more set up

// array for main site navigation; 
// keys are the names of the items as they appear on the menu, values are their local URL value
$p_sections = array();
$s_sections = array();

// add menu items for each card deck
foreach ($decks as $item=> $value) {
	$p_sections["{$value['title']}"] = 'play.php?suit=' . $item;
	$s_sections["{$value['title']}"] = 'show.php?suit=' . $item;
}

?>