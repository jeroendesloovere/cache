<?php
/**
 * Cache test
 *
 * This Cache PHP class allows you to cache pages and data for a certain time.
 * Is a stand-alone class to create speed-win on the server snamee.
 *
 * @author Jeroen Desloovere <jeroen@siesqo.be>
 */

// autoload files using Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use JeroenDesloovere\Cache;

// define dummy data
$data = array(
    array(
        'id' => 1,
        'text' => 'first item'
    ),
    array(
        'id' => 2,
        'text' => 'second item'
    )
);

// [optional] set the path where the cache should be saved to
//Cache::setCachePath($_SERVER['DOCUMENT_ROOT'] . '/cache/');

// [optional] set the extension for the cache files
//Cache::setCacheExtension('.cache');

// set data to cache
Cache::setData('blog', 'articles', $data);

// get data from cache
$items = Cache::getData('blog', 'articles');
?>

<?php if(Cache::start('home-page', 'widget-recent-blog-articles-nl')):?>

<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Cache PHP class test</title>
</head>
<body>
    <p>Test-output</p>

    <?php if(isset($items)):?>
        <ul>
        <?php foreach($items as $item):?>
            <li><?php echo $item['id'] . '-' . $item['text'];?></li>
        <?php endforeach;?>
        </ul>
    <?php endif;?>
</body>
</html>

<?php Cache::stop(); endif;?>
