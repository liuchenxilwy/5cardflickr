Five Card Flickr Stories
=========================
by Alan Levine http://cogdogblog.com/

ABOUT
-----
Create your own version of the original Five Card Flickr Stories at http://5card.cogdogblog.com

Designed to foster visual thinking, it is based completely on the Five Card Nancy game devised by comics guru Scott McCloud and the nifty web version at 741.5 Comics.

However, rather than drawing from a hand of randomly chosen panels of the old Nancy comics, my version draws upon collections of photos specified by a tag in flickr. You are dealt five random photos for each draw, and your task is to select one each time to add to a selection of images, that taken together as a final set of 5 images- tell a story in pictures.

When you are done, you have the option to add a title and explanation, and save the story so you can put a link in your resume or send to your Mom (she pay print it out and tape it to the fridge, or she may criticize your creativity, your mileage and mom may vary). Plus we offer the ability to tweet your story or use an embed code to add it to your own web site.

Requirements
------------

This site needs PHP and a MySQL database, plus you will need to set up a Flickr API key. If I can do this, you can.

Setting Up
----------
1. Copy all files to your web server in directory of your choice. 
2. Set permissions on `5cardstats.inc` to be writable
3. Create an empty MYSQL database
4. Import into your database the set up bits in `5card.sql`  to create the two database tables needed to work some magic.
5. Edit `config.php` to set things up- all items are commented and should not take much to understand. But then again, I hate beets.
6. There won;t be much unless you have a good number of photos tagged in flickr. Open the URL for `admin.php` to trigger an update call to flickr.
7. You will probably want to have the site updated itself. Change the name of flickr-fetch.php to anything that is not as guessable, and set up a cron script to run it once an hour, e.g. 

`15 * * * * curl http://www.yoursite.com/5cardflickr/sneaky-pete-update.php'

Yes This is Barebones
---------------------
It's version 1 of the github code. gimme a break!



