<?php
/*
Five Card Flickr Site
by Alan Levine, cogdogblog@gmail.com
inspired completely by Five-Card Nancy http://www.7415comics.com/nancy/

Five Card Flickr is Copyright  Alan Levine and is distributed under the terms of the GNU General Public License Code Available from http://www.gnu.org/licenses/gpl.html

Code available from https://github.com/cogdog/5cardflickr

This admin page is used for site owners to trigger refreshes and such.
It's not very elegant.


=============================================================================
*/
  

// ------- CONFIG --------------------------------------------------------------

require_once('utils.php');
require_once('config.php');

// page title
$my_title  = 'Five Card Flickr Admin';


// Use session variable to track logins
session_start(); 

// set admin login based on session variable
$admin = (isset($_SESSION['5cardadmin'])) ? 1 : 0;

$error_message = "";
$msg = "The kingdom is yours!";



if (!$admin) {
	if ($_REQUEST['login']) { 
		$pass = strip_tags(trim($_POST['pass'])); 
		
		if ( $pass == '') { 
			// no password entered
			$error_message .= 'Password missing';
			
		} elseif ( $pass != ADMINKEY) { 
		
			// mis match
			$error_message .= 'Password incorrect';
		} else {
			// we are good to go, set session variable
			$_SESSION['5cardadmin'] = 'ok';
			$msg = "Successfully logged in.";
			$admin = 1;
		}
	
	} else {
		$msg = "Log in if you will.";
	}
} elseif ($_REQUEST['logout']) { 
		unset($_SESSION['5cardadmin']);
		header('Location: index.php');

}

$tag = '52cards';

if (isset($_POST['fetch']) ) {

	$tag = $_POST['tag'];
	$maxdate = isset($_POST['maxdate']) ? $_POST['maxdate'] : '';
	
}

if (isset($_POST['check']) ) {
	$furl = $_POST['furl']; 
}

include( 'header.php' );
?>

</head>
<body>

<?php include 'nav.inc'?>



<div id="content-wrapper">

<h2><?php echo $my_title?></h2>

<?php if (!$admin) :?>

	<p><?php echo $msg ?> <span style="color:red; font-weight:bold;"><?php echo $error_message ?> </span></p>
	
		<form method="post" action="admin.php">
		

			<label for="pass">Password</label>
			<input type="password" name="pass" size="20" /> <input type="submit" value="Login" name="login" />
		</form>

<?php else:?>
<p><?php echo $msg ?> [<a href="admin.php?logout=1">logout</a>]</p>

<h3>Dashboard</h3>

<table id="newspaper-b" summary="Latest Stats for this Site">
<thead>
<tr>
	<th scope="col">name</th>
	<th scope="col">tag</th>
	<th scope="col">stories</th>
	<th scope="col">photos</th>
</tr>
</thead>
<tbody>
<?php
	foreach ($decks as $suit=>$item) {
		
		$storycount = get_tbl_count($db, 'stories', "deck='{$item['tag']}'");
		$photocount = get_tbl_count($db, 'cards', "tag='{$item['tag']}'");

		echo "<tr><td><a href=\"play.php?suit=$suit\" target=\"_blank\">{$item['title']}</a></td><td>{$item['tag']}</td><td><a href=\"show.php?suit=$suit\" target=\"_blank\">$storycount</a></td><td><a href=\"photos.php?tag={$item['tag']}\" target=\"_blank\">$photocount</a></td></tr>\n";
	
	}


?>
</tbody>
</table>


<h3>Flickr Fetch</h3>
<p><?php include '5cardstats.inc';?></p>
<p>Be kind to the API!</p>

<form method="post" action="admin.php">

Fetch for <select name="tag">


<option value="52cards" <?php if ($tag == '52cards') echo ' selected'?>>All Suits</option>

<?php
foreach ($decks as $suit=>$item) {
	$selected = ($tag == $item['tag']) ? ' selected' : '';
	echo '<option value="'. $item['tag'] . '"' . $selected . '>' . $item['title'] . '</option>';
}
?>
</select><br /><br />

The flickr API returns the 100 most recent photos. To go back and time for older ones, enter a date that photos should be older than. (enter a date in the form of "May 12, 2010" for 100 photos uploaded before that date)<br />
<input name="maxdate" value="<?php echo $maxdate?>" /><br /><br />
<input type="submit" name="fetch" value ="Fetch from flickr" />
</form>

<?php if (isset($_POST['fetch'])) :?>
<?php
	// get an array of the tags we need to fetch for; default is to run them all
	$the_tags = ($_POST['tag'] == '52cards' ) ? get_all_tags($decks) : array($_POST['tag']);
	
	
	
	$update_count = 0;
	
	// walk through the tags
	foreach($the_tags as $tag) {

		$start_time = time();
		$new_photos = get_from_flickr($db, CARD_DECK, $tag, 1, $maxdate);
		$update_count += $new_photos;
	

		echo "<p>At " . date('r') . " $new_photos photos added to the cards for tag '$tag'. Execution time= " . (time() - $start_time)  . " seconds.</p>";

	}
?>

<?php if ($update_count):?>
<h3>Updated Dashboard</h3>

<table id="newspaper-b" summary="Latest Stats for this Site">
<thead>
<tr>
	<th scope="col">name</th>
	<th scope="col">tag</th>
	<th scope="col">stories</th>
	<th scope="col">photos</th>
</tr>
</thead>
<tbody>
<?php
	foreach ($decks as $suit=>$item) {
		
		$storycount = get_tbl_count($db, 'stories', "deck='{$item['tag']}'");
		$photocount = get_tbl_count($db, 'cards', "tag='{$item['tag']}'");

		echo "<tr><td><a href=\"play.php?suit=$suit\" target=\"_blank\">{$item['title']}</a></td><td>{$item['tag']}</td><td><a href=\"show.php?suit=$suit\" target=\"_blank\">$storycount</a></td><td><a href=\"photos.php?tag={$item['tag']}\" target=\"_blank\">$photocount</a></td></tr>\n";
	
	}


?>
</tbody>
</table>
<?php endif; // updated dashbaord?>

<?php endif; // fetched new photos?>

<h3>Mark Photo as Missing</h3>
<p>Take a photo out of the pool</p>

<form method="post" action="admin.php">
Enter flickr URL for photo to mark as missing<br />
<input type="text" name="furl" />

<input type="submit" name="check" size="40" value ="Mark Missing" />
</form>

<?php if ( isset($_POST['check']) ) {

	echo '<p>' . mark_photo_dead ($db, CARD_DECK, trim($furl) ) . '<br />for photo <a href="' . $furl . '">' . $furl . '</a></p>';
		
}
?>






<?php endif; // is admin??>
	
</div>	
	
	
	<?php include 'footer.inc'?>	
</div>
	
</body>
</html>
