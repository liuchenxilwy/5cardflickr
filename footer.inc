	<div id="footer">
	
		<div id="meta" class="across">
		
			<div class="recent">
			
				<h3>Random Five Card Stories</h3>
				
				<ul>
				<?php
				
				$rstories = get_rand_stories($db, 3);
				
				foreach ($rstories as $item) {
				
					// truncate long titles
					$title = ( strlen($item['title']) > 30 ) ? substr( $item['title'], 0,29) . '...' : $item['title'];
					
					echo '<li><a href="show.php?id=' . $item['id'] . '">' . stripslashes($title) . '</a></li>';
				}
				?>
				
				<li class="more"><a href="show.php">more...</a></li>
				</ul>
			
			</div>
			
			<div class="upcoming">
			
				<h3>Photos</h3>
				
				<ul>
					<li><a href="http://flickr.com/photos/tags/5cardflickr">flickr tagged 5cardflickr</a></li>

					<li><a href="http://www.flickr.com/groups/visualstory/">Tell a Story in Five Frames</a></li>

					<li><a href="http://flickr.com/photos/cogdog">CogDogBlog Photos</a></li>

				</ul>
			
			</div>
			
			<div class="authors">
			
				<h3>Wag More</h3>
				
				<ul>
					<li><a href="http://cogdogblog.com/">CogDogBlog</a></li>
					<li><a href="http://50ways.wikispaces.com/">50+ Web 2.0 Ways to tell a Story</a></li>
					<li><a href="http://cogdogblog.com/stuff/nv09/">Say/Blog It in Pictures</a></li>
				</ul>
			
			</div>
			
			<hr />
		
		</div>
	
		<div id="copyright">
		
		<?php echo $my_title?>: http://<?php echo "{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}"; if ($_SERVER['QUERY_STRING']) echo '?' . $_SERVER['QUERY_STRING']?><br>
			<a href="http://web.nmc.org/5cardstory">five card flickr</a> is a <a href="http://cogdogblog.com/">CogDogBlog</a> production  | web design based on <a href="http://www.oswd.org/design/preview/id/3364">Chary</a> by <a href="http://www.nikhedonia.com/" title="SimplyGold">Sadhana Ganapathiraju</a> 

		</div>


<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-29227240-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>