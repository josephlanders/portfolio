Hi,

How does the system work?
-------------------------

Movies and details get scraped once from the remote server,
then cached in the database and in memcached.

Thus, we can set a scrape expiry time for both movie list and movie details 
and force an update the database/memcached if necessary.

For instance, we might decide to scrape the movie lists twice a day or
to scrape the movie details every 30 mins to check for price updates.


Design limitations due to API limitations
------------------------------------

1.) No price on listings page

The reason is that we have to issue too many get movie details requests on first load when the cache is clear.
This could cause long page load times.

Detailed analysis:
I saw the spec said allow customers to get the best price.

This IMHO would involve getting the price from each provider for a specific movie
and displaying it on the listings page.

However, the external API would need to show the price in the Movie List.

Alternatively or also, it would need to allow multiple "movie details" requests to save Round Trip Time (RTT).

Otherwise, the number of requests on first load with a cleared cache is too high.

2.) I would prefer to have one movie image on the movie listings page and multiple providers listed.
 However, as per 1.) This can't be done without multiple get movie details requests.


Things missing
---------------

1.) Code is missing comments
2.) Tests are not comprehensive - only test the retrieval logic from the different caches.
3.) Database Fields all use VARCHAR(256) (something that wouldn't normally do, but I am time constrained)
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
