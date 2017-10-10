<?php
/*
Five Card Flickr Site
by Alan Levine, cogdogblog@gmail.com
inspired completely by Five-Card Nancy http://www.7415comics.com/nancy/

Five Card Flickr is Copyright 2008 Alan Levine and is distributed under the terms of the GNU General Public License Code Available from

http://code.google.com/p/fivecardflickr/

photos.php
called to display all photos tracked associated with a given tag

*/  

// ------- SETUP ---------------------------------------------------------------
require_once('utils.php');   	// commons code
require_once('config.php'); 	// configuration


$b= PHOTOPAGER;	 // number of photos per display to show

// starting point in list of photos
if (isset($_REQUEST['idx'])) {
	$idx = ($_REQUEST['idx']);
	
	if (!is_numeric($idx)) die ("Input data error 703");
} else {
	$idx = 0;
}



if ($_REQUEST['tag']) {
	$flickr_tag  = $_REQUEST['tag'];
} else {
	$flickr_tag  = $decks["$default_deck"]['tag'];

}	

$my_title = 'Photos Tagged &quot;' . $flickr_tag . '&quot;' ;

$params = "tag=" . $flickr_tag;

// get page nav lnks
$photo_count = get_tbl_count($db, 'cards', "tag='$flickr_tag'");
$page_nav = get_set_links($idx,$photo_count, $b,'photos.php?' . $params);


$displaycount = ( $photo_count < $b ) ? 'all' : $b;


include( 'header.php' );
?>

</head>
<body>

<?php include 'nav.inc'?>

<div id="content-wrapper">
<h2><?php echo $my_title?></h2>


<p align="right"><?php echo $page_nav?></p>

<p>Browse <strong><?php echo $displaycount?></strong> of the <strong><?php echo $photo_count?></strong> photos available on this site tagged <strong><?php echo $flickr_tag?></strong> (more may be spotted on <a href="http://flickr.com/photos/tags/<?php echo $flickr_tag?>" target="_blank">flickr</a>)...</p>


<?php

$photos = get_all_photos($db, $idx, $b, "tag='" . $flickr_tag . "' " );

$pcount = 0;

foreach ($photos as $item) {
	$pcount++;
	$pic = get_image_info($db,$item['id'],$mode='all');

	echo '<a href="' . $pic['link'] . '" target="flicked"><img src="' . $pic['sq'] . '" title="flickr photo by ' . $pic['credit'] . '" /></a>';
	
	if ($pcount == 10) {
		echo '<br />';
		$pcount = 0;
	}
}

?>
<p align="right"><?php echo $page_nav?></p>

</div>


	
<?php include 'footer.inc'?>	
	
	</div>


	
</body>
</html>







