Hi,

Due to the API design, I can't design the site as I'd prefer :~(

Therefore, I've just put the price on the movie details page.

The ideal thing would be to display the "Price" on the homepage but we'd have to do multiple requests to get the 13 item prices and this would add to page load time.

If we had the price in the movie listing, we could use one image and have a setup similar to Amazon 

such as:

Phantom menance from $5.99 from 5 providers.

Alternatively with some JQuery we could overlay the multiple prices but that's messy.

Cheers,
Joseph


Setup instructions? I don't think you should try to set this up! :P

It needs apache, php, php-memcache, memcache, mariadb and modifications to php.ini for memcache extension.