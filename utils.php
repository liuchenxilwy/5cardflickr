<?php

/* Five Card Flickr function library

Five Card Flickr
by Alan Levine, cogdogblog@gmail.com

*/

/* ----------- DB_CONNECT -----------------------------------
Called to connect to mySQL database
-------------- DB_CONNECT ----------------------------------- */

function db_connect($db,$script=null) {
	$ln = @mysql_connect(MYSQL_HOST,MYSQL_USER,MYSQL_PASS);
	if ( $ln && mysql_select_db($db,$ln) ) {
		return ($ln);
	}
	else {
		// if connected to server then database error
		// $failure = ($ln) ? 'database: '. $db : ' MySQL Server';
		// $msg = "Can't connect to the $failure.\n\nMySQL reports:\n".mysql_error();
		// if (!is_null($script)) $msg .= "\n\nError occurred in: $script";
		// mail(CHIEF,'5 Card MySQL error',$msg,'From: '.CHIEF."\nX-Mailer: PHP/".phpversion());
		return false;
	}
}


/* ----------- CLEANSTRING_FOR_DB -------------------------------------------
clean up strings added to database- remove any HTML and clean for quotes, etc
-------------- CLEANSTRING_FOR_DB ------------------------------------------- */

function cleanstring_for_db($str) {
	return mysql_real_escape_string(strip_tags($str));
}


/* ----------- GET_FROM_FLICKR ---------------------------------
Use flickr API to check a tag for new images; only add ones that
are not in the database. We will do multiple database inserts in
batches of 10
-------------- GET_FROM_FLICKR --------------------------------- */

function get_from_flickr($db, $card_deck, $tag, $verbose=false, $maxdate='') {

	require_once("phpFlickr.php");
	$f = new phpFlickr(FLICKRKEY);

	if ($maxdate =='') {
		// get flickr photos for search on the tag
		$found = $f->photos_search( array("tags"=>$tag, "safe_search" => 1) );
		
		if ($verbose) echo "<p><strong>Starting fetch for photos tagged '$tag' processed at " . date('-r') . '</strong></p><ul>';
	} else {
	
		// get flickr photos for search on the tag and less than $maxdate
		$found = $f->photos_search(array("tags"=>$tag, "safe_search" => 1, "max_upload_date" => strtotime($maxdate)));
		
		if ($verbose) echo "<p><strong>Starting fetch for photos tagged '$tag' and uploaded before '$maxdate' processed at " . date('-r') . '</strong></p><ul>';
	}
	
	
	// track number of photos added
	$cnt=0;
	
	// array to hold mysql query values
	$values = array();
	
	foreach ($found['photo'] as $photo) {
		
		// skip a photo that's already in the cards database
		if  (get_tbl_count($db, $card_deck, 'fid="' . $photo['id'] . '"')) continue;
		
		// we run an insert when we have 10 values to insert
		if (count($values) == 10) {
		
			if ($verbose) echo '<li>  --- Inserting next 10 photos into card database... --- </li>';
			$query = "INSERT INTO $card_deck (fid,farm,server,secret,nsid,username,tag) VALUES " . implode(',',$values);
			$result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
			
			// reset array
			$values = array();
		}
		
		// get photo owner
		$owner = $f->people_getInfo($photo['owner']);
		
		// add value strings for query
		$values[] = "('" . 
			$photo['id'] . "', " . 
			$photo['farm'] . ", " . 
			$photo['server'] . ", '" . 
			$photo['secret'] . "', '" . 
			$photo['owner'] . "', '" . 
			addslashes($owner['username']) . "', '" . 
			$tag . "')";
		
		//update count
		$cnt++;
		
		if ($verbose) echo '<li><img src="' . "http://farm" . $photo['farm'] . ".static.flickr.com/" . $photo['server'] . "/" . $photo['id'] . "_" . $photo['secret'] . '_m.jpg" /> Processing flickr photo <a href="http://www.flickr.com/photos/' . $photo['owner'] . '/' . $photo['id'] . '/">' . 'http://www.flickr.com/photos/' . $photo['owner'] . '/' . $photo['id'] .   '/</a> by ' . $owner['username'] . '</li>';
		
		
	}
	
	// if there are pending inserts in the queue, then add them
	if (count($values)) {
		
		$query = "INSERT INTO $card_deck (fid,farm,server,secret,nsid,username,tag) VALUES " . implode(',',$values);
		$result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
			if ($verbose) echo '<li>  --- Inserting last photos into card database... --- </li>';
	}
	
	if ($verbose) echo '</ul>';
	
	return($cnt);

}


/* ----------- DEAD_PHOTO_CHECK --------------------------------
set a photo's active flag to false based on flickr url
-------------- DEAD_PHOTO_CHECK --------------------------------- */

function mark_photo_dead ($db, $card_deck, $furl ) {

	// get last part of URL from http://stackoverflow.com/a/5984365/2418186
	
	// if last character is "/" remove it
	if ( substr( $furl, -1) == "/" ) {
		$furl = substr($furl, 0, -1);
	} 
	
     $keys = parse_url($furl); // parse the url
     $path = explode("/", $keys['path']); // splitting the path
     $fid = end($path); // get the value of the last element, the flickr id

	$query = "UPDATE $card_deck SET active=0 WHERE fid = $fid";
	$result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
	// return number of rows updated
	return ( mysql_info() );
}


/* ----------- GET_TBL_COUNT -----------------------------------------------
General purpose function to get numbers from a table, with
option to provide conditions
-------------- GET_TBL_COUNT ----------------------------------------------- */

function get_tbl_count($db, $table, $conditions = 1) {
	$query = "SELECT COUNT(*) FROM $table WHERE $conditions";
	$result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
	$row = mysql_fetch_row($result);
	return ($row[0]);
}


/* ----------- GET_PICS -------------------------------------------------------
Get a set of new random images from database

$current: an array of the flickr image ids (from the main database) 
	      already picked for the current user (so we avoid dupes)
	      
num: number of random cards to return

-------------- GET_PICS ----------------------------------------------------- */


function get_pics($db,$tag,$current,$num=5) {
	
	// holder for pictures selected
	$pics = array();
	
	// condition to search on to avoid picking dupes
	$cond = (count($current)) ? 'id !=' . implode(" AND id !=", $current) : '1';
	
	
	$card_deck = CARD_DECK; // database table for photos
	
	// query for random selection from database
	$query = "SELECT * FROM $card_deck WHERE $cond and tag='$tag' and active=1 ORDER BY RAND() limit 0,$num";
	
	$result = mysql_query($query, $db);	
	if (mysql_error() ) echo 'Ouch! Database problem:' . mysql_error();
	
	 while ($row = mysql_fetch_array($result)) {
	 	$pics[] = array(
	 		'id' => $row['id'], 
	 		'url' => "http://farm" . $row['farm'] . ".static.flickr.com/" . $row['server'] . "/" . $row['fid'] . "_" . $row['secret'] . "_m.jpg",
	 		'link' => "http://www.flickr.com/photos/" . $row['nsid'] . "/" . $row['fid'] . "/",
	 		'credit' => stripslashes($row['username']),
	 		);
    }

	return $pics;
}


/* ----------- SAVE_STORY ----------------------------------------------------
Save story date to database. The card ids are the 5 database ids for the photos, 
a comma separated string
-------------- SAVE_STORY --------------------------------------------------- */


function save_story($db, $card_ids, $tag, $title, $name, $comments) {
	
	$query = "INSERT INTO stories 
					SET 
					deck ='" . $tag . "', 
					cards ='$card_ids', 
					title = '" . cleanstring_for_db($title) . "', 
					name ='" . cleanstring_for_db($name) . "', 
					created = NOW(),
					comments = '" . cleanstring_for_db($comments) . "'";
					
	$result = mysql_query($query, $db);	
	if (mysql_error() ) echo 'Ouch! Database problem:' . mysql_error();
	
	return (mysql_insert_id());
	
}

/* ----------- GET_IMAGE_INFO -------------------------------------------------
For a given database id from the photos table ('cards'), extract the parts needed
to contruct just a url for the thumbnal (if $mode=url) otherwise, build both the
URL for the thumbnail, the url to point to the flickr page for the photo, and 
the name of the flickr user who posted the photo
-------------- GET_IMAGE_INFO -------------------=--------------------------- */


function get_image_info($db,$id,$mode='url') {

	$card_deck = CARD_DECK; // database table for photos
	
	// get the url for a flickr image of a given id	
	$query = "SELECT * FROM $card_deck WHERE id=$id";

	
	$result = mysql_query($query, $db);	
	if (mysql_error() ) echo 'Ouch! Database problem:' . mysql_error();
	
	$row = mysql_fetch_array($result);
	
	if ($mode=='url') {
		// return just url for medium
		return "http://farm" . $row['farm'] . ".static.flickr.com/" . $row['server'] . "/" . $row['fid'] . "_" . $row['secret'] . "_m.jpg";
		
	} elseif ($mode=='sq') {
	// return just url for thumbnail
		return "http://farm" . $row['farm'] . ".static.flickr.com/" . $row['server'] . "/" . $row['fid'] . "_" . $row['secret'] . "_s.jpg";
		
		
		
	} elseif ($mode=='p') {
	// pecha moder return just url for medium 640 size
		return "http://farm" . $row['farm'] . ".static.flickr.com/" . $row['server'] . "/" . $row['fid'] . "_" . $row['secret'] . "_d.jpg";	
	
	} else {
		// return all photo info
		$pic= array();

    	$pic['url']= "http://farm" . $row['farm'] . ".static.flickr.com/" . $row['server'] . "/" . $row['fid'] . "_" . $row['secret'] . "_m.jpg";
    	
    	$pic['med'] = "http://farm" . $row['farm'] . ".static.flickr.com/" . $row['server'] . "/" . $row['fid'] . "_" . $row['secret'] . "_d.jpg";
    	
    	// build link to flickr page for this photo
		$pic['link']="http://www.flickr.com/photos/" . $row['nsid'] . "/" . $row['fid'] . "/";
		
		$pic['credit']=stripslashes($row['username']);
		
		// url for square icon
		$pic['sq'] = "http://farm" . $row['farm'] . ".static.flickr.com/" . $row['server'] . "/" . $row['fid'] . "_" . $row['secret'] . "_s_d.jpg";

		return $pic;
	}

}

function get_image_info_array($row) {
	/* for a given array of photo information from the cards database,
	   returns the flickr info used for output (just to keep code cleaner and make
	   formatting easier if flickr changes stuff down the road) 
	   
	   expected array values are all fields for one row from cards database
	*/
	
	$pic= array();

	$pic['url']= "http://farm" . $row['farm'] . ".static.flickr.com/" . $row['server'] . "/" . $row['fid'] . "_" . $row['secret'] . "_m.jpg";
	
	// build link to flickr page for this photo
	$pic['link']="http://www.flickr.com/photos/" . $row['nsid'] . "/" . $row['fid'] . "/";
	
	$pic['credit']=stripslashes($row['username']);
	
	// url for square icon
	$pic['sq'] = "http://farm" . $row['farm'] . ".static.flickr.com/" . $row['server'] . "/" . $row['fid'] . "_" . $row['secret'] . "_s_d.jpg";

	return $pic;

}

/* ----------- GET_ALL_PHOTOS --------------------------------------------
Find all photos that match a given $cond
-------------- GET_ALL_PHOTOS-------------------=------------------------ */

function get_all_photos($db, $idx, $batch, $cond='1') {
	 $allphotos = array();

	 $query = "SELECT * FROM cards WHERE $cond and active=1 ORDER by id DESC  LIMIT $idx, $batch";
	 
	 
	 $result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
	 
	 while ($row = mysql_fetch_array($result)) {
    	$allphotos[] = $row;
    }
	
    return ($allphotos);
}


function get_pecha_photos($db, $cond='1', $idx=0, $batch=20) {
	 $allphotos = array();

	 $query = "SELECT id FROM cards WHERE $cond and active=1 ORDER by RAND() LIMIT $idx, $batch";
	 
	 
	 $result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
	 
	 while ($row = mysql_fetch_array($result)) {
    	$allphotos[] = $row[0];
    }
	
    return ($allphotos);
}




/* ----------- GET_STORY ----------------------------------------------------
Get a single story
-------------- GET_STORY -------------------=------------------------------- */


function get_story($db, $id) {

	 $query = "SELECT deck, cards, name, title, UNIX_TIMESTAMP(created) as created, comments FROM `stories` WHERE id='$id'";
	 $result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
	 $story = mysql_fetch_array($result);
     return ($story);	
}

/* ----------- GET_ALL_STORIES --------------------------------------------
Find all stories that match a given $cond
-------------- GET_ALL_STORIES-------------------=------------------------ */

function get_all_stories($db, $idx, $batch, $cond='1') {
	 $allstories = array();

	 $query = "SELECT id, deck, name, title, UNIX_TIMESTAMP(created) as created FROM stories WHERE $cond ORDER by id DESC  LIMIT $idx, $batch";
	 
	 
	 $result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
	 
	 while ($row = mysql_fetch_array($result)) {
    	$allstories[] = $row;
    }
	
    return ($allstories);
}

/* ----------- GET_RAND_STORIES --------------------------------------------
Extract a random number of stories (used for the footer)
-------------- GET_RAND_STORIES -------------------=------------------------ */


function get_rand_stories($db, $num) {
	$stories= array();
	
	$query = "SELECT * FROM `stories` WHERE 1 ORDER BY RAND() limit 0,$num";
	$result = mysql_query($query, $db);	
	if (mysql_error() ) echo 'Ouch! Database problem:' . mysql_error();
	 while ($row = mysql_fetch_array($result)) {
    	$stories[] = $row;
    }
    
    return ($stories);
}

function get_story_embed($db, $id, $js=false) {
	$story = get_story($db, $id);

	$url_path = 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . '/';
	
	$out = 'document.write(\'<div class="fivecardstory">\');';
	
	$out = '<div class="fivecardstory">';
	$out .= '<p><strong><a href="' . $url_path . 'show.php?id=' . $id . '">'  . $story['title']  . '</a></strong><br />';
	$out .= '<em>a <a href="' . $url_path . '">five card flickr story</a> by ' . $story['name'] . '</em></p>';
	$out .= '<p><a href="' . $url_path . 'show.php?id=' . $id . '">';
	
	$mycards = explode(',', $story['cards']);
	
	foreach ($mycards as $id) {
		$card = get_image_info($db,$id,$mode='sq');
		$out .= '<img src="' .  $card . '" alt="" class="5cardimg" /> ';
	}
	
	$out .= '</a></div>';
		
	if ($js) {
		return ('document.write(\'' . $out . '\');');
	} else {
		return ($out);
	}
	

}

/* ----------- GET_STORY_LINKS ----------------------------------------------
Generate navigation links for a single story that link to the previous and next
ones in the database, as well as link to the random story a
-------------- GET_STORY_ LINKS-------------------=-------------------------- */

function get_story_links($db, $id) {

	// build the output string
	$str = '<strong>stories: </strong>';
	
	// find the story id prior to the current one
	$query = "SELECT id FROM `stories` WHERE id < $id order by id desc Limit 0,1";
	$result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
	 
	
	
	if (mysql_num_rows($result)) {
		// build a link if there was a previous story found
		$row = mysql_fetch_row($result);
	 	$str .= '<a href="show.php?id=' . $row[0] . '">prev</a> | ';
	} else {
		// no link for the first id
		$str .= 'prev | ';
	}
	
	
	// add link for random
	$str .=  '<a href="random.php">random</a> | ';
	
	// find the story id after the current one	
	$query = "SELECT id FROM `stories` WHERE id > $id order by id asc Limit 0,1";
	$result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
	 
	 
	if (mysql_num_rows($result)) {
		// build a link if there was a previous story found
		$row = mysql_fetch_row($result);
	 	$str .= '<a href="show.php?id=' . $row[0] . '">next</a>';
	} else {
		// no link for the last id
		$str .= 'next';
	}

	return $str;

}



/* ----------- GET_OTHER_STORIES --------------------------------------------
find other stories that use the same images as story $id 
-------------- GET_OTHER_STORIES -------------------=------------------------ */


function get_other_stories($db, $id, $cards) {
	
	$allstories = array();
	
	// query for all stories that have the same 5 cards, but exclude story $id
	 $query = "SELECT id, name, title FROM `stories` WHERE cards = '$cards' AND id!=$id ORDER by id DESC";
	 $result = mysql_query($query, $db) or die ("Error Error! " . mysql_error(). " in query<br>$query");
	 
	 while ($row = mysql_fetch_array($result)) {
    	$allstories[] = $row;
     }
     
	return ($allstories);
}

/* ----------- GET_SET_LINKS --------------------------------------------
Generate the paged links for navigation

  $idx is the starting point
  $total is the count of all items
  $batch is how many are included in a page of links
  $url is the base part of the links created
-------------- GET_SET_LINKS -------------------=------------------------ */


function get_set_links($idx,$total,$batch,$url) {

	$conj = ( strchr($url,'?') ) ? '&' : '?';
	
	// remove any idx= in the URL string
	$url = preg_replace('/&idx=([0-9]+)/', '', $url);
	
	// rewind link
	 $first = ( !$idx || $idx <  $batch )  ? '' : '&laquo; <a href="'.$url .'">first</a> ';

	# set prev link
	$prev = ( !$idx || $idx == 0 ) ? 'prev' : '<a href="'.$url.$conj.'idx='.($idx-$batch).'">prev</a>';
	
	// fast forward link
	$last =  ( $total > 10 * $batch )  ? ' <a href="'.$url.$conj.'idx='. (floor($total/$batch) * $batch) .'">last</a> &raquo' : '';

	# set next link
	$next = ( ($total - $idx) <= $batch ) ? 'next' : '<a href="'.$url.$conj.'idx='. ($idx + $batch ) .'">next</a>';
	
	// check upper limit for links
	$upper = min ( 10, floor($total/$batch) );

	if ($total > $batch) {
		for ($i = ( $idx/$batch ); $i < ( $idx/$batch ) + $upper; $i++) {
			if (($i*$batch) == $idx)
				$links .= ' <strong>'.($i+1).'</strong>';
			else
				$links .= ' <a href="'.$url.$conj.'idx='.($i*$batch).'">'.($i+1).'</a>';
		}
		
		return "$first &lt; $prev :: $links :: $next &gt; $last";
		
	} else {
		return "";
	
	}

	

}

/* ----------- GET_SUIT_FROM_TAG --------------------------------------------
A little bit of a hack to get the array key from our $decks data structure
for a given tag that is used within
-------------- GET_SUIT_FROM_TAG -------------------=------------------------ */


function get_suit_from_tag($decks,$tag,$def) {
	foreach ($decks as $key => $value) {
		if ($value['tag'] == $tag) return $key;
	}	
	return $def;
}

/* ----------- GET_ALL_TAGS --------------------------------------------
Set an array of all tags used on the site
-------------- GET_ALL_TAGS -------------------=------------------------ */


function get_all_tags($decks) {
	$all_tags = array();
	foreach ($decks as $key => $value) {
		$all_tags[] = $value['tag'];
	}	
	return $all_tags;
}



/* ----------- IS_TAG ---------------------------------------------------------
Test to see if a certain tag is used on this site
-------------- IS_TAG --------------------------=--------------------------- */

function is_tag($decks,$tag) {
	// verfifies that a tag is defined 
	foreach ($decks as $key => $value) {
		if ($value['tag'] == $tag) return true;
	}	
	return false;
}

/* ----------- TWEET_STORY ----------------------------------------------------
// build a twitter status message for story $id, with title=$title, and message
string for the tweet ($msg).  A #5cardflickr hashtag is added.

If the total length is more than 140 chars, we truncate the title of the story
-------------- ITWEET_STORY --------------------------=------------------------ */


function tweet_story($id,$title,$msg) {
	
	// build the URL for the story
	$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?id=' . $id;
	
	// get length of string for message and URL (includes space in between)
	$cnt = strlen($url) + strlen($msg) + 1;
	
	// chars available for title
	$whats_left = 140-$cnt;
	
	if (strlen($title) <  $whats_left) {
		// We have enough room for the full title, return the encoded part of the twitter
		//    status message
		return urlencode($msg . ' ' . $title . ' ') .  $url . urlencode(' #5cardflickr');
		
	} else {
		// shorten the title
		return urlencode($msg . ' ' . substr($title, 0 , $whats_left-3)  . '... ') .  $url . urlencode(' #5cardflickr');
	}

}

?>