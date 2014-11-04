# Cache PHP class

This Cache PHP class allows you to cache pages and data for a certain time.
Is a stand-alone class to create speed-win on the server.

## Installation

### Using Composer

When using [Composer](https://getcomposer.org) you can always load in the latest version.

```json
{
    "require": {
        "jeroendesloovere/cache": "1.1.*"
    }
}
```
> Check [in Packagist](https://packagist.org/packages/jeroendesloovere/cache).

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

## Documentation

The class is well documented inline. If you use a decent IDE you'll see that each method is documented with PHPDoc.

## Contributing

It would be great if you could help us improve this class. GitHub does a great job in managing collaboration by providing different tools, the only thing you need is a [GitHub](http://github.com) login.

* Use **Pull requests** to add or update code
* **Issues** for bug reporting or code discussions
* Or regarding documentation and how-to's, check out **Wiki**
More info on how to work with GitHub on help.github.com.

## License

The module is licensed under [MIT](./LICENSE.md). In short, this license allows you to do everything as long as the copyright statement stays present.