# Cache PHP class

This Cache PHP class allows you to cache pages and data for a certain time.
Is a stand-alone class to create speed-win on the server side.


## Functions

### Caching page-parts

```
Cache::start($group, $id, $lifetime = false, $overwrite = false);
Cache::end();
```


### Caching data

```
Cache::setData($group, $id, $data, $lifetime = false);
Cache::getData($group, $id, $overwrite = false);
```


### Clearing cache

```
Cache::clear();
```


### Setting cache output

```
Cache::setCachePath();
```


### Setting cache file extension

```
Cache::setCacheExtension('.tpl');
```
