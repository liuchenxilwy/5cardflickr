<div id="header"><h1><a href="index.php"><?php echo SITENAME ?></a></h1></div>

	
	
<ul id="nav" class="dropdown dropdown-horizontal">

	<li><a href="index.php">Home</a></li>

	<li><a href="show.php">Gallery of Stories</a></li>
	<li><a href="play.php">Play a Round</a>
		<ul>		
			<?php
			
				foreach($p_sections as $key => $value) {		
					echo "<li><a href=\"$value\">$key</a></li>";
				}
			?>
		</ul>
	</li>

	<li><a href="show.php">Show Stories</a>
	
		<ul>
		<?php
					foreach($s_sections as $key => $value) {
						echo "<li><a href=\"$value\">$key</a></li>";
					}
			?>
		</ul>
	</li>
	
	<li><a href="prompts.php">Prompts</a>
	
		<ul>
			<li><a href="prompts.php?suit=5card">Five Card Flickr</a></li>
			<li><a href="prompts.php?suit=etmooc">#etmooc</a></li>
			<li><a href="prompts.php?suit=tdc">The Daily Create</a></li>
		
		</ul>
	
	
	</li>
	
	<li><a href="random.php">Random Story</a></li>
	<li><a href="http://code.google.com/p/fivecardflickr/">Source Code</a></li>
	
	
	<?php if (GTRANSKEY !='') : // display google translation menu?>
	<li>
	<div id="google_translate_element"></div>
	<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
}
</script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
	</li>
	<?php endif?>
	
</ul>
