<?php
/*
Five Card Flickr Site
by Alan Levine, cogdogblog@gmail.com
inspired completely by Five-Card Nancy http://www.7415comics.com/nancy/

Five Card Flickr is Copyright 2008 Alan Levine and is distributed under the terms of the GNU General Public License Code Available from

http://code.google.com/p/fivecardflickr/

*/  



// ------- SETUP ---------------------------------------------------------------
require_once('utils.php');   	// commons code
require_once('config.php'); 	// configuration


$b= PAGER;	 // number of stories per display to show

// starting point in list of stories
if (isset($_REQUEST['idx'])) {
	$idx = ($_REQUEST['idx']);
	
	if (!is_numeric($idx)) die ("Input data error 703");
} else {
	$idx = 0;
}

if ($_REQUEST['id']) {
	if (!is_numeric($_REQUEST['id'])) die ('Input data error 1412');
	$my_story = get_story($db, $_REQUEST['id']);
	$my_title = 'Five Card Story: ' . $my_story['title'];
	
	$flickr_tag = $my_story['deck'];
	
	$suit = get_suit_from_tag($decks,$flickr_tag,$default_deck);
	
	$errors=0;
	
	$page_nav = get_story_links($db, $_REQUEST['id']);

	
} elseif ($_REQUEST['suit']) {
	$suit = $_REQUEST['suit'];
	
    $my_title = $decks[$suit]['title'] . ' Story Gallery';
    
    $flickr_tag = $decks[$suit]['tag'];
	$params = "suit=" . $suit;
	
    // get page nav lnks
    $story_count = get_tbl_count($db, 'stories', "deck='$flickr_tag'");
    $cardcount = get_tbl_count($db, 'cards', "tag='$flickr_tag'");
    
	$page_nav = get_set_links($idx,$story_count, $b,'show.php?' . $params);

} elseif ($_REQUEST['p']) {
	$p = $_REQUEST['p'];
	// get all stories that use a specified photo

	$pic = get_image_info($db,$p ,'all');
	
	$pic['url'] = str_replace('_m.jpg', '_d.jpg',$pic['url']);
	
	$params = "p=" . $p;

	$my_title = 'Stories that use this photo shared by ' . stripslashes($pic['credit']);
	
	$story_count = get_tbl_count($db, 'stories', "cards LIKE '$p,%' OR cards LIKE  '%,$p,%' OR cards LIKE  '%,$p'");
	$page_nav = get_set_links($idx,$story_count, $b,'show.php?'. $params);
	
} else {
	$my_title = 'Five Card Story Gallery';

	$story_count = get_tbl_count($db, 'stories');
	$page_nav = get_set_links($idx,$story_count, $b,'show.php');
}

include( 'header.php' );
?>

</head>
<body>

	<?php include 'nav.inc'?>


<div id="content-wrapper">

<h2><?php echo $my_title?></h2>


<?php if ($_REQUEST['id']):?>
<p><?php echo $page_nav?></p>
<p><em>a <a href="show.php?suit=<?php echo $suit?>"><?php echo $decks[$suit]['title']?> story</a> by <?php echo stripslashes($my_story['name'])?> created <?php echo date("M d Y, h:i:s a", $my_story['created'])?>. <a href="play.php?suit=<?php echo $suit?>">Create a new one</a>!</em></p>

	<div class="content">
	
<?php
	$cnt = 0;
	$pcredits = '';
	$show_cards = '';
	$full_url = $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
	
	// string to create copy/paste version of story
	$embed_cards = '<hr /><h2><a href="http://' . $full_url . '?'  .  $_SERVER['QUERY_STRING'] . '">' . $my_title . '</a></h2><p>a <a href="http://' . $full_url . '?suit=' . $suit. '">' .  $decks[$suit]['title']. '</a> story created by ' . stripslashes($my_story['name']) . "</p>\n";
	
	
	$mycards = explode(',', $my_story['cards']);
	
	foreach ($mycards as $id) {
		$card = get_image_info($db,$id,$mode='all');
		$cnt++;
		$show_cards .= '<p class="card"><a href="' . $card['link'] . '" class="drop-shadow"><img src="' . $card['url'] . '" height="150" class="captioned p' . $cnt . '" /></a></p>';	
		
		$embed_cards .= '<p><a href="' . $card['link'] . '"><img src="' . $card['med'] . '" /></a><br>flickr photo by <a href="' . $card['link'] . '" target="_blank">' .  $card['credit'] . '</a></p>'. "\n";	
		
		$pcredits .= '(' . $cnt . ') <a href="' . $card['link'] . '" target="_blank">' .  $card['credit'] . '</a> ';
		
	}
	
	$show_cards .= '<br clear="all" /><p><strong>flickr photo credits:</strong> ' . $pcredits . '</p>';
	
	
	echo $show_cards;
	
	
	$embed_cards .= '<p><em>' . nl2br(stripslashes($my_story['comments'])) . "</em></p>\n<hr />\n";

	
?>

<hr /></div>

<h4>about this story</h4>
<p><em><?php echo nl2br(stripslashes($my_story['comments']))?></em></p>


<h4>share this story</h4>

<p>
<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
<a href="http://twitter.com/share" class="twitter-share-button" data-count="none" data-via="">Tweet this story.</a></p>

<p><strong>permalink to story:</strong> http://<?php echo $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?id=' . $_REQUEST['id']?></p>


<?php 

$other_versions = get_other_stories($db, $_REQUEST['id'], $my_story['cards']);

if (count($other_versions)) {
	echo '<h4>other stories made from the same cards</h4><ul>';
	foreach ($other_versions as $item) {
		echo '<li><a href="show.php?id=' . $item['id'] . '">' . stripslashes($item['title']) . '</a> by ' .  stripslashes($item['name']) . "</li>\n";
	
	}
	
	echo '</ul>';

}


?>

<h4>Copy/Paste Story</h4>
<p>Click once to select, then copy and paste HTML to your own blog/website.
<form actio="#">
<textarea name="nada" cols="80" rows="10" onClick="this.select()"><?php echo $embed_cards?></textarea>
</form>



<h2 style="margin-top:2em;">create a different story from these same cards</h2>
<p>Do you have another interpretation of the story behind these pictures? Add it to the collection as a new story!</p>

<div class="content">
<?php echo $show_cards;?>
</div>


<form method="post" action="play.php" name="save">
<input type="hidden" name="ids" value="<?php echo $my_story['cards']?>">
<input type="hidden" name="suit" value="<?php echo $suit?>">

<h4><label for="title">Title for Story</label></h4>
<input name="title" type="text" size="60" maxlength="72" />
<h4><label for="name">Your name or nickname (surely you want credit!)</label></h4>
<input name="name" type="text"  size="60" maxlength="64" />
<h4><label for="comments">Comments or explanation of story (all HTML will be removed)</label></h4>
<textarea name="comments" rows="12" cols="60"></textarea>

<?php if ($use_captcha):?>
<p>For security purposes, please enter the correct words matching the images (blame the spammers):</p>

<div class="g-recaptcha" data-sitekey="<?php echo $sitekey?>"></div>

<?php endif?>


<input name="save" type="submit" value="Save My Story">
</form>

<p align="right"><?php echo $page_nav?></p>






<?php elseif ($_REQUEST['suit']):?>
<p align="right"><?php echo $page_nav?></p>

<p>Browse the <?php echo $story_count . ' ' . $decks[$suit]['title']?> stories based on the <a href="photos.php?tag=<?php echo $decks[$suit]['tag']?>"><?php echo $cardcount?> flickr photos tagged with <?php echo $decks[$suit]['tag']?></a>...</p>

<ol start="<?php echo $idx + 1?>">

<?php

$stories = get_all_stories($db, $idx, $b, "deck='" . $flickr_tag . "' " );

foreach ($stories as $item) {
	echo '<li><a href="show.php?id=' . $item['id'] . '">' . $item['title'] . '</a>  created on ' .  date("M d Y, h:i:s a T", $item['created']) . ' by ' . $item['name'] . '</li>';
}

?>
</ol>
<p align="right"><?php echo $page_nav?></p>


<?php elseif ($_REQUEST['p']):?>
<p align="right"><?php echo $page_nav?></p>

<p class="card"><a href="<?php echo $pic['link']?>" target="_blank" class="drop-shadow"><img src="<?php echo $pic['url']?>" alt="flickr photo by <?php echo $pic['credit']?>" class="captioned"  /></a><br />(<a href="<?php echo $pic['link']?>" target="_blank">flickr photo by <?php echo $pic['credit']?></a>)</p>

<p>Browse the <?php echo $story_count?> stories that use this image:</p>

<ol start="<?php echo $idx + 1?>">
<?php
$stories = get_all_stories($db, $idx, $b, "cards LIKE '$p,%' OR cards LIKE '%,$p,%' OR  cards LIKE '%,$p'");

foreach ($stories as $item) {
	echo '<li><a href="show.php?id=' . $item['id'] . '">' . $item['title'] . '</a>  created on ' .  date("M d Y, h:i:s a T", $item['created']) . ' by ' . $item['name'] . '</li>';
}
?>

</ol>
<p align="right"><?php echo $page_nav?></p>

<?php else:?>
<p align="right"><?php echo $page_nav?></p>

<p>Browse all of the <?php echo $story_count?> stories created so far on this web site.</p>


<ol start="<?php echo $idx + 1?>">

<?php

$stories = get_all_stories($db, $idx, $b);

foreach ($stories as $item) {
	
	$suit = get_suit_from_tag($decks,$item['deck'],$default_deck);
	
	echo '<li><a href="show.php?id=' . $item['id'] . '">' . $item['title'] . '</a> a ' .  $decks[$suit]['title'] . ' story created on ' .  date("M d Y, h:i:s a T", $item['created']) . ' by ' . $item['name'];
	
	echo '</li>';
}

?>
</ol>

<p align="right"><?php echo $page_nav?></p>


<?php endif?>

</div>
	
<?php include 'footer.inc'?>	
	
	</div>


<script type="text/javascript">
// Create the tooltips only on document load
$(document).ready(function() 
{
   // By suppling no content attribute, the library uses each elements title attribute by default
   $('.content img.p1').qtip({
      content: {
         text: 'See <a href="show.php?p=<?php echo $mycards[0]?>">all stories</a> with this photo'
      },
      
      position: {
		  corner: {
			 target: 'leftTop',
			 tooltip: 'bottomLeft'
		  }
	   },
	   
	   hide: { when: 'mouseout', fixed: true },
	   
   
      style: 'dark' // Give it some style
   });
   
   
    $('.content img.p2').qtip({
      content: {
         text: 'See <a href="show.php?p=<?php echo $mycards[1]?>">all stories</a> with this photo'
      },
      
      position: {
		  corner: {
			 target: 'leftTop',
			 tooltip: 'bottomLeft'
		  }
	   },
	   
	   hide: { when: 'mouseout', fixed: true },
	      
      style: 'dark' // Give it some style
      
      });
      
     $('.content img.p3').qtip({
      content: {
         text: 'See <a href="show.php?p=<?php echo $mycards[2]?>">all stories</a> with this photo'
      },
      
      position: {
		  corner: {
			 target: 'leftTop',
			 tooltip: 'bottomLeft'
		  }
	   },
	   
	   hide: { when: 'mouseout', fixed: true },
	   
   
      style: 'dark' // Give it some style
	 });
	 
    $('.content img.p4').qtip({
      content: {
         text: 'See <a href="show.php?p=<?php echo $mycards[3]?>">all stories</a> with this photo'
      },
      
      position: {
		  corner: {
			 target: 'leftTop',
			 tooltip: 'bottomLeft'
		  }
	   },
	   
	   hide: { when: 'mouseout', fixed: true },
	   
   
      style: 'dark' // Give it some style

   });
   
   $('.content img.p5').qtip({
      content: {
         text: 'See <a href="show.php?p=<?php echo $mycards[4]?>">all stories</a> with this photo'
      },
      
      position: {
		  corner: {
			 target: 'leftTop',
			 tooltip: 'bottomLeft'
		  }
	   },
	   
	   hide: { when: 'mouseout', fixed: true },
	   
   
      style: 'dark' // Give it some style
	});
   
});
</script>


<script type="text/javascript" src="js/jquery.qtip-1.0.0-rc3.min.js"></script> 
	
</body>
</html>