<?php
/*
Five Card Flickr Site
by Alan Levine, cogdogblog@gmail.com
inspired completely by Five-Card Nancy http://www.7415comics.com/nancy/

Five Card Flickr is Copyright  Alan Levine and is distributed under the terms of the GNU General Public License Code Available from http://www.gnu.org/licenses/gpl.html

Code available from https://github.com/cogdog/5cardflickr

prompts.php
Displays a set of prompts for photo uploads

*/  



// ------- CONFIG --------------------------------------------------------------

require_once('utils.php');   	// common code library

require_once('config.php'); 	// configuration files for entire site


// ------- SETUP ---------------------------------------------------------------

// get the card deck to use, use default if not provided
$suit = (isset($_REQUEST['suit'])) ? $_REQUEST['suit'] : '5card';

// set the flickr tag
$flickr_tag = $decks[$suit]['tag'];

$storycount = get_tbl_count($db, 'stories', "deck='$flickr_tag'");
$cardcount = get_tbl_count($db, 'cards', "tag='$flickr_tag'");

$tagline = 'As of ' . date("M d Y, h:i:s a T") . ' there have been <a href="show.php?suit=' . $suit . '">' . $storycount . ' ' . $decks[$suit]['title'] . ' Stories</a> created from the pool of <a href="photos.php?tag=' . $flickr_tag . '">' . $cardcount . ' flickr photos tagged with "' . $flickr_tag . '"</a>';

// page title

$my_title = 'Prompts for ' . $decks[$suit]['title'] . ' Photo Sharing';
include( 'header.php' );
?>

</head>
<body>

<?php include 'nav.inc'?>



<div id="content-wrapper">
<h2><?php echo $my_title?></h2>
<p><small><?php echo $tagline?></small></p>
<p>To make an interesting pool of photos for this series, below are some suggested prompts for the kinds of ones to share on flickr. Just be sure to tag them <?php echo $flickr_tag?>.</p>

<?php include 'content/' . $suit?>

<p>When you are ready, try and <a href="play.php?suit=<?php echo $suit?>">play a round of 5card flickr now!</a></p>





	</div>
	
<?php include 'footer.inc'?>	
	
	</div>
	
</body>
</html>
