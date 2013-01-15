Monktemple
==========

Dashboard
---------

* Response time graph (Github like)
	* This will be constructed from parsing `access.log` of sites
		* Might need to add custom parameters in logging configuration
* Awstats (free, bunch of not so useful stats but good to have)
* Memcache stats
	* `telnet localhost 11211` and then `stats`
* Varnish stats
	* `varnishstat` & `varnishtop` [https://www.varnish-cache.org/docs/2.1/tutorial/statistics.html#tutorial-statistics](https://www.varnish-cache.org/docs/2.1/tutorial/statistics.html#tutorial-statistics)
	* Monitoring using `munin` & a bunch of related interesting links - [http://blog.gomiso.com/2011/01/04/easy-monitoring-of-varnish-with-munin/](http://blog.gomiso.com/2011/01/04/easy-monitoring-of-varnish-with-munin/)
		* `munin` also generates charts
	* Graphs using `Graphite` - [https://github.com/redsnapper8t8/pyvarnish](https://github.com/redsnapper8t8/pyvarnish)
* APC stats
	* It does have a page to show stats, but we might need look into something better or itegrate data into our common view
		* Possible to pull data, saw something about a plugin for integrating this in virtualmin which is a cpanel script for servers


Granular Control
----------------
* Clearing APC
	* Doable from custom code. Will grant access to user through monktemple's dashboard.
* Clearing Memcached
	* Doable from magento admin itself!
* Clearing Varnish cache
	* Looking into usable modules. Sandeep has tried a couple of them.
	* If needed we can explore doing this via `VCL` i.e. programmatically, if possible.


Local.xml
---------

* We need to change the memcahed cache prefix specified in `local.xml` (Not an immediate concern as only clean is running over there)
	* Didn't find prefix node on clean's staging, weird!
	* Also need to explore further parameters in `local.xml` related to caching


Nginx
-----

* Need super dope config files for Magento (Inspiration [http://www.kingletas.com/2012/08/full-page-cache-with-nginx-and-memcache.html](http://www.kingletas.com/2012/08/full-page-cache-with-nginx-and-memcache.html))
	* Denies access to magento specific files
		* `local.xml`
		* `.git` directory
		* `app`, `var` etc
	* Denies access to xml


APC
---

* Our APC configuration looks good as we have a `100%` hit ratio, so not sure if there is anything we can do to improve performance.
* Clearing cache from terminal is diff from frontend cache. Doesn't work if PHP is using mod_php but in our case it should work.
* Need a way to clear APC's code on a per site basis.
	* Possible by clearing on a directory basis - http://stackoverflow.com/a/8720302/551713
	* Use [apc_cache_info](http://www.php.net/manual/en/function.apc-cache-info.php) to get the list of cached files. Call `apc_delete_file` on any files that match your mask.
	* You can also use an [APCIterator](http://www.php.net/manual/en/apciterator.construct.php) to find all files that match your mask and then delete them. Note that you'll want to move the iterator to the next file before you delete the previous one. Or make an array of all matching filenames using the iterator and then delete them from your own array. Modifying a collection while traversing it is tricky.
* `apc.stat` parameter is the one with which APC picks up the file has changed and discards its cache immediately. Its turned off in production so as to get rid of the small performance overhead in checking whether the file has changed since the last time it was cached.
	* Ability to toggle this would be ideal.


Memcached
---------

* Right now we have only 1 instance of memcached running. We need 2. One holds only the sessions, and other one holds the usual cached stuff.
	* Important because memcached can be cleared from magento admin and we don't want current sessions being thrown out of the window.
	* Also gives us the ability to refresh memcached at will, without worrying about killing current sessions.
	* Also storing sessions in `tmpfs` filesystem is fastest [http://magebase.com/magento-tutorials/magento-session-storage-which-to-choose-and-why/](http://magebase.com/magento-tutorials/magento-session-storage-which-to-choose-and-why/)
* `@TODO` Need to research on 2 level cache in Magento (thanks Zend!)
	* Its limitation - http://www.fabrizio-branca.de/magento-zend-frameworks-twolevels-cache-backend-mess.html
	* A patch to improve it? - https://gist.github.com/1262502
	* Explanation of the 2 levels (slow/fast) and the option of disabling the slow all together if you have enough RAM: http://www.byte.nl/blog/speeding-up-magento-the-burden-of-two-level-cache/

Varnish
-------

* Forgot the exact need but we would require to write some Varnish config file code (which is a language in itself - `VCL`)
	* `@TODO` Update this!


MySQL optimizations
-------------------

* Query cache

`query_cache_type=1`
`query_cache_size=64M`


Testing tools
-------------

* [www.magespeedtest.com](http://www.magespeedtest.com)
* pingdom transaction monitor: https://www.pingdom.com/transactionmonitor/

Guidelines for us
-----------------

* Always restart services gracefully wherever possible with minimal damage to traffic


Magento's internals
-------------------

* Optimization links at the bottom - [http://magebase.com/magento-tutorials/speeding-up-magento-with-apc-or-memcached/](http://magebase.com/magento-tutorials/speeding-up-magento-with-apc-or-memcached/)
* [http://www.sonassi.com/knowledge-base/magento-kb/what-is-memcache-actually-caching-in-magento/](http://www.sonassi.com/knowledge-base/magento-kb/what-is-memcache-actually-caching-in-magento/)
* [http://www.kingletas.com/2012/09/how-does-magento-full-page-cache-works.html](http://www.kingletas.com/2012/09/how-does-magento-full-page-cache-works.html)
* [http://www.kingletas.com/2012/11/unleash-the-power-of-automation.html](http://www.kingletas.com/2012/11/unleash-the-power-of-automation.html)
* [http://www.kingletas.com/2012/09/magento-developers-toolbox-v2-0.html](http://www.kingletas.com/2012/09/magento-developers-toolbox-v2-0.html)
* [http://www.kingletas.com/2012/08/magento-developers-toolbox-magento-php-console.html](http://www.kingletas.com/2012/08/magento-developers-toolbox-magento-php-console.html)
