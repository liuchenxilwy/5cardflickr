<?php

/*
Five Card Flickr Site
by Alan Levine, cogdogblog@gmail.com
inspired completely by Five-Card Nancy http://www.7415comics.com/nancy/

Five Card Flickr is Copyright  Alan Levine and is distributed under the terms of the GNU General Public License Code Available from http://www.gnu.org/licenses/gpl.html

Code available from https://github.com/cogdog/5cardflickr

redirects to a randome story

=============================================================================
*/


// ------- SETUP ---------------------------------------------------------------
require_once('utils.php');   	// commons code
require_once('config.php'); 	// configuration

$story = get_rand_stories($db, 1);

header('Location:show.php?id=' . $story[0]['id']);

?>