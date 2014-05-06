<?php
/*
Five Card Flickr Site
by Alan Levine, cogdogblog@gmail.com
inspired completely by Five-Card Nancy http://www.7415comics.com/nancy/


Five Card Flickr is Copyright  Alan Levine and is distributed under the terms of the GNU General Public License Code Available from http://www.gnu.org/licenses/gpl.html

Code available from https://github.com/cogdog/5cardflickr


=============================================================================
*/
  

// ------- CONFIG --------------------------------------------------------------

require_once('utils.php');
require_once('config.php');


$my_title  = 'Five Card Flickr';
include( 'header.php' );
?>

</head>
<body>

<?php include 'nav.inc'?>

<div id="content-wrapper">

	<h2><?php echo $my_title?></h2>
	<img src="images/5-card-nancy.jpg" alt="" width="240" height="163" align="right" />
	<p><?php include '5cardstats.inc';?></p>

	<p><strong>This web site is designed to foster visual creativity by making stories out of photos.</strong> It is based completely on the <a href="http://www.scottmccloud.com/inventions/nancy/nancy.html">Five Card Nancy game</a> devised by comics guru <a href="http://www.scottmccloud.com/">Scott McCloud</a> and the <a href="http://www.7415comics.com/nancy">nifty web version at 741.5 Comics</a>.</p>

	<p>However, rather than using randomly chosen panels of the old Nancy comic, my version draws upon collections of photos specified by a tag in <a href="http://flickr.com/">flickr</a>. You are dealt five random photos for each draw, and your task is to select one each time to add to your building set of images, that taken together as a final set of 5 - tell a story in pictures.</p>

	<p>When you are done, you can add a title and explanation, and save the story. You can easily put a link in your resume or send to your Mom (she may print it out and tape it to the fridge, or she may criticize your creativity, your mileage and mom may vary). Plus we offer the ability to tweet your story or use an embed code to add it to your own web site.</p>

	<?php
	if (get_tbl_count($db, 'stories')) {
		$story = get_rand_stories($db, 1);
		echo get_story_embed($db, $story[0]['id']);
	}
	?>

	<p>What do stories look like? What a fantastic question you ask!</p> We have <a href="show.php">a growing collection</a> of mixed bag stories, but for an example see a  <a href="random.php">random</a> one such as we have inserted here. </p>

	<p>The code for this site is opensource and available at <a href="https://github.com/cogdog/5cardflickr">https://github.com/cogdog/5cardflickr</a></p>

	<p>So what are you waiting for? <a href="play.php">Take a fling at weaving a tale with pictures only!</a></p>

	<p>And if you liked Five Card Flickr, try doing something similar with photos and audio- see <a href="http://johnjohnston.info/flickrSounds/">John Johnston's flickrsounds</a>.

</div> <!--// content-wrapper -->
	
	<?php include 'footer.inc'?>	
</div>
	
</body>
</html>
