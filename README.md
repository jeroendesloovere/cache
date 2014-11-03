# Cache PHP class

This Cache PHP class allows you to cache pages and data for a certain time.
Is a stand-alone class to create speed-win on the server.

## Functions

### Caching page-parts

``` php
Cache::start($folder, $name, $lifetime = false, $overwrite = false);
Cache::end();
```

### Caching data

``` php
Cache::setData($folder, $name, $data, $lifetime = false);
Cache::getData($folder, $name, $overwrite = false);
```

### Setting cache output

``` php
Cache::setCachePath($path);
```

### Setting cache file extension

``` php
Cache::setCacheExtension('.tpl');
```
