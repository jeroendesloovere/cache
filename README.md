# Cache PHP class

This Cache PHP class allows you to cache pages and data for a certain time.
Is a stand-alone class to create speed-win on the server snamee.

## Functions

### Caching page-parts

```
Cache::start($folder, $name, $lifetime = false, $overwrite = false);
Cache::end();
```

### Caching data

```
Cache::setData($folder, $name, $data, $lifetime = false);
Cache::getData($folder, $name, $overwrite = false);
```

### Setting cache output

```
Cache::setCachePath($path);
```

### Setting cache file extension

```
Cache::setCacheExtension('.tpl');
```
