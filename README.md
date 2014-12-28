asset-discovery
===============

_asset-discovery_ is a Composer-enabled PHP script to locate assets
(JS, CSS, Coffee, SASS, images) across multiple PHP components which then
can be copied automatically into generated stylesheets, scripts and
image folders.

Based on [component-discovery](https://github.com/soundasleep/component-discovery).

## Configuring

First include `asset-discovery` as a requirement in your project `composer.json`,
and run `composer update` to install it into your project:

```json
{
  "require": {
    "soundasleep/asset-discovery": "dev-master"
  }
}
```

Now create a `asset-discovery.json` in your project, to define the types of assets to discover,
and where to place source files:

```json
{
  "src": ["vendor/*/*", "core"],
  "js": "site/generated/js/generated.js",
  "coffee": "site/generated/js/generated-coffee.coffee",
  "css": "site/generated/css/generated.css",
  "scss": "site/generated/css/generated-scss.scss",
  "images": "site/generated/images/"
}
```

_asset-discovery_ will look in all the `src` folders for files called `assets.json`
to find matching assets. Wildcards are supported. For example, in your
`vendor/my/package/assets.json`:

```json
{
  "scss": ["css/currencies.scss", "css/second.scss"],
  "coffee": ["js/*.coffee"],
  "images": ["images/*"]
}
```

Generated asset files, other than images, will be included in the source order specified.

## Building

Run the generate script, either with your build script or manually, with
a given root directory:

```
php -f vendor/soundasleep/asset-discovery/generate.php .
```

This will generate various files under the directories defined in your `asset-discovery.json` config.
These files can then be passed along to the next step in a build chain (e.g. compile SASS to CSS,
minify, spritify, optimize images etc).

## TODOs

1. Actually publish on Packagist
2. More documentation, especially default `asset-discovery.json` parameters
3. Tests
4. Example projects using _asset-discovery_
5. Create `grunt` task `grunt-php-asset-discovery` to wrap the manual PHP command
6. Release 0.1 version
