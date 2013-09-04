<?php
/*
Five Card Flickr Site
by Alan Levine, cogdogblog@gmail.com
inspired completely by Five-Card Nancy http://www.7415comics.com/nancy/

Five Card Flickr is Copyright  Alan Levine and is distributed under the terms of the GNU General Public License Code Available from http://www.gnu.org/licenses/gpl.html

Code available from https://github.com/cogdog/5cardflickr

play.php
This script runs the stirybuilding operation using the tag passed to it; one script can do many different iterations of 5 card flickr.

The Javascript constructor manages a list of the database ids for the images as they are picked.

*/  

// ------- CONFIG --------------------------------------------------------------

require_once('utils.php');   	// common code library

require_once('config.php'); 	// configuration files for entire site


// ------- SETUP ---------------------------------------------------------------

// get the card deck to use, use default if not provided
$suit = (isset($_REQUEST['suit'])) ? $_REQUEST['suit'] : $default_deck;

// set the flickr tag
$flickr_tag = $decks[$suit]['tag'];

$storycount = get_tbl_count($db, 'stories', "deck='$flickr_tag'");
$cardcount = get_tbl_count($db, 'cards', "tag='$flickr_tag'");

$tagline = 'As of ' . date("M d Y, h:i:s a T") . ' there have been <a href="show.php?suit=' . $suit . '">' . $storycount . ' ' . $decks[$suit]['title'] . ' Stories</a> created from the pool of <a href="photos.php?tag=' . $flickr_tag . '">' . $cardcount . ' flickr photos tagged with "' . $flickr_tag . '"</a>';



// put users collected images so far into an array
$my_ids = (isset($_POST['ids'])) ? explode(',', $_POST['ids']) : array();

// page title

switch (count($my_ids)) {

	case 0:
		$my_title = 'Are you ready to play Five Card flickr?';
		break;
	case 5:
		$my_title =  'Five Card flickr completed story';
	
	
		if ($use_captcha) {
			// set up recaptcha
			require_once('recaptchalib.php');		
			
			# the response from reCAPTCHA
			$captcha_resp = null;
			# the error code from reCAPTCHA, if any
			$captcha_error = null;
		}
	
		$errors=0;
	
		break;
	default:
		$my_title = 'Five Card flickr draw ' . (count($my_ids)+1) . ' of 5';
}

// override for final story
if ($_POST['save']) {
	// check for missing field values

	// blank user name error check
	if ( $_POST['title'] == '') { 
			$errors++;
			$error_message .= '<li>Please include a title for your story.</li>';
	}
	
	if ( $_POST['name'] == '') { 
			$errors++;
			$error_message .= '<li>Please enter your name so you can get you all the glory and fame you deserve.</li>';
	}
	
	if ( strlen($_POST['comments']) < 30 ) { 
			$errors++;
			$error_message .= '<li>Hey! Where is your story? Write something that explains the sequence of pictures. You can type in at least 30 characters, right?</li>';
	}
	
	if ( stripos($_POST['comments'], 'http://') !== false ) { 
			$errors++;
			$error_message .= '<li>Tsk tsk tsk, URLs are not permitted. You may want to take your spamming efforts elsewhere.</li>';
	}
	
	
	// captcha check
	
	if ($use_captcha) {
		if ($_POST["recaptcha_response_field"] or $_POST["recaptcha_response_field"] == '') {
			$captcha_resp = recaptcha_check_answer ($privatekey,
											$_SERVER["REMOTE_ADDR"],
											$_POST["recaptcha_challenge_field"],
											$_POST["recaptcha_response_field"]);
		
				if (!$captcha_resp->is_valid) {
						# set the error code so that we can display it
						$errors++;
						$captcha_error = $captcha_resp->error;
						$error_message .= '<li>Captcha error. Please enter the words that are displayed.</li>';
				}
		}
	}
	
	$my_title = ($errors) ? 'Ooops please fix a few things...' : 'Your five card flickr story was saved!';

}

include( 'header.php' );
?>

<script type="text/javascript" language="Javascript">

function pick(id) {
	// called from selection of image to add to story, add the new pic id to our local form variables
	
	// is our deck empty?
	if (document.picker.ids.value == '') {
	
		// add new card to deck, including url to sm image, link to original and credit
		document.picker.ids.value = id;
	} else {
		
		// append new card to end of deck
		document.picker.ids.value +=  ',' + id;
	}	
	document.picker.submit();
}

</script>
</head>
<body>

<?php include 'nav.inc'?>



<div id="content-wrapper">
<h2><?php echo $my_title?></h2>
<p><small><?php echo $tagline?></small></p>

<?php if (count($my_ids)): ?>

<?php

echo '<div class="content">';


if ( count($my_ids) == 5) {
	
	$cnt = 0; 			// counter
	$pcredits = '';  	// holder for the string of credits
	
	foreach ($my_ids as $id) {
		$card = get_image_info($db,$id,$mode='all');
		$cnt++;
		echo '<p class="card"><a href="' . $card['link'] . '" class="drop-shadow"><img src="' . $card['url'] . '" height="150" class="captioned" /></a></p>';	
		
		$pcredits .= '(' . $cnt . ') <a href="' . $card['link'] . '" target="_blank">' .  $card['credit'] . '</a> ';
	}
	
	echo '<hr /></div><p><strong>flickr photo credits:</strong> ' . $pcredits . '</p>';
	
	
} else {

	// steps 2-5; just fetch an image  {
	foreach ($my_ids as $id) {
		echo '<p class="card"><a href="#"  class="drop-shadow"><img src="' . get_image_info($db,$id) . '" height="150"  class="captioned"/></a></p>';
	}
	
	echo '<hr /></div><p>';
	
}

?>
<?php endif?>

<?php if (count($my_ids) < 5):?>
<p><strong>Pick an image to add it to your story</strong></p>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>" name="picker">
<input type="hidden" name="ids" value="<?php echo $_POST['ids'] ?>">
<input type="hidden" name="tag" value="<?php echo $flickr_tag?>">
<input type="hidden" name="suit" value="<?php echo $suit?>">


<div class="content">

<?php 
$new_cards = get_pics($db, $flickr_tag, $my_ids, 5);
$cnt = 0;

$pcredits='';
foreach ($new_cards as $item) {
	$cnt++;
	echo '<p class="card"><a href="#" onClick="pick(\'' . $item['id'] . '\'); return false;" class="drop-shadow"><img src="' . $item['url'] . '" height="150" class="captioned" /></a></p>';
	
	$pcredits .= '(' .  $cnt . ') <a href="' . $item['link'] . '" target="_blank">' .  $item['credit'] . '</a> | ';
}
?>
<hr /></div><p><strong>flickr photo credits:</strong> <?php echo $pcredits?></p>

<?php elseif (isset($_POST['save']) AND $errors==0):?>



<?php  
$my_story_id = save_story($db, $_POST['ids'], $flickr_tag, $_POST['title'], $_POST['name'], $_POST['comments']); 
mysql_close();

$my_link = 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . '/show.php?id=' . $my_story_id;
?>

<p>Your five card flickr story has been saved!
<strong><?php echo stripslashes($_POST['title'])?></strong> <br />
created by <strong><?php echo stripslashes($_POST['name'])?></strong> on <?php echo date("M d Y, h:i:s a")?><br />
<em><?php echo nl2br(stripslashes($_POST['comments']))?></em></p>

<h4>share this story</h4>
<p>
<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
<a href="http://twitter.com/share" class="twitter-share-button" data-text="My Five Card Flickr Story: <?php echo stripslashes($_POST['title']);?>" data-url="<?php echo $my_link?>" data-count="none" data-via="">Tweet this story.</a></p>






<p><strong>permalink to story:</strong>  <a href="<?php echo $my_link?>"><?php echo $my_link?></a>


<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
<input name="save" type="submit" value="Try Again?">
<input type="hidden" name="tag" value="<?php echo $flickr_tag?>">
<input type="hidden" name="suit" value="<?php echo $suit?>">

</form>

<?php else:?>

<h3>Share it!</h3>

<?php 


if ($errors) {
	echo '<ul>' . $error_message . '</ul>';
} else {
	echo '<p>Congratulations! Your story is complete. Would you like to save it for prosperity?</p>';
}?>



<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>" name="save">
<input type="hidden" name="ids" value="<?php echo $_POST['ids'] ?>">
<input type="hidden" name="tag" value="<?php echo $flickr_tag?>">
<input type="hidden" name="suit" value="<?php echo $suit?>">


<h4><label for="title">Title for Story</label></h4>
<input name="title" type="text" size="60" maxlength="72" value="<?php echo $_POST['title']?>" />
<h4><label for="name">Your name or nickname (surely you want credit!)</label></h4>
<input name="name" type="text"  size="60" maxlength="64" value="<?php echo $_POST['name']?>" />
<h4><label for="comments">Comments or explanation of story (all HTML will be stripped, URLs are not allowed)</label></h4>
<textarea name="comments" rows="12" cols="60"><?php echo $_POST['comments']?></textarea>

<?php if ($use_captcha):?>
<p>For security purposes, please enter the correct words matching the images (blame the spammers):</p>

<?php echo recaptcha_get_html($publickey, $captcha_error); ?>
<?php endif?>

<input name="save" type="submit" value="Save My Story">
</form>
<?php endif?>

	</div>
	
<?php include 'footer.inc'?>	
	
	</div>
	
</body>
</html>
