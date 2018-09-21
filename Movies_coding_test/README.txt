Hi,

How does the system work?
-------------------------

Movie lists and details get scraped once from the remote server(s),
then cached in the database and in memcached.

Thus, we can set a scrape expiry time for both movie list and movie details 
and force an update to the database/memcached if necessary.

For instance, we might decide to scrape the movie lists twice a day to 
check for new movies or to scrape the movie details every 30 mins to check
for price updates.


Design limitations due to API limitations
------------------------------------

1.) No price on listings page

The reason is that we have to issue too many get movie details requests 
on first load when the cache is clear. This could cause long page load times.

Suggestion:
 API redesign to provide price in movie list or
 API redesign to provide multiple details {cw123123,fw124134,cw124123} in one request.

2.) Multiple Movie listings instead of collating movies with the same "name (year)"

This is because of 
 1.) The points above.
 2.) Code complexity.
 3.) Not knowing what to do with the "images" from different providers and which
 would take precedence.


Things missing
---------------

1.) Code is missing comments
2.) Tests are not comprehensive - only briefly test the retrieval logic from the different caches.
 Mostly to show that I can write tests :P
3.) Database Fields all use VARCHAR(256)
4.) CSS is not in CSS file :P

I have time constraints and would normally iterate over this and add the above.


Assumptions
-----------

"name (year)" is unique for each real world movie. 
(No two movies with the same name produced in the same year)


Setup instructions
------------------
I don't think you should try to set this up! :P

It needs apache, php, php-memcache, memcache, php-mbstring, mariadb and modifications to php.ini for memcache extension.


Running the tests
-----------------

Something like this:

c:\xampp\php\php.exe phpunit-7.3.5.phar --bootstrap .\vendor\autoload.php SmallTest.php

Linux might be:
php ./phpunit-7.3.5.phar --bootstrap .\vendor\autoload.php SmallTest.php

Cheers,
Joseph