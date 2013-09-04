<?php

// require
require_once('../cache.php');

// [optional] set the path where the cache should be saved to
Cache::setCachePath($_SERVER['DOCUMENT_ROOT'] . '/cache/');

// set data to cache
//Cache::setData('blog', 'articles', array('items' => 'test'));

// get data from cache
//$items = Cache::getData('blog', 'articles');

// start caching
//Cache::start('home-page', 'widget-recent-blog-articles-nl'); 

// stop caching
//Cache::end('home-page', 'widget-recent-blog-articles-nl');