# Sass For Symfony!

This bundle make it easy to use Sass with Symfony's AssetMapper Component
(no Node required!).

- Automatically downloads the correct Sass binary
- Adds a `sass:build` command to build and watch your Sass changes

## Installation

Install the bundle

```shell
composer require symfonycasts/sass-bundle
```

## Usage

Start by writing your first Sass file `assets/styles/app.scss`, and let's add some basic style

```scss
// assets/styles/app.scss

$red: #fc030b

body {
  background: $red;
}
```

Then point your styles in your template.

```php
{# templates/base.html.twig #}

{% block stylesheets %}
    <link rel="stylesheet" href="{{ asset('styles/app.scss') }}">
{% endblock %}
```

That's right! You point directly to the `.scss` file. But don't worry, the final built `.css` file will be returned!

Then run the command:

```shell
php bin/console sass:build --watch
```

And that's it!

## How Does it work

The first time you run one of the Sass commands, the bundle will download the correct Sass binary for you system in to `bin/dart-sass`
directory.

When you run `sass:build`, that binary is uses to compile Sass file into a
`var/sass/app.built.css` file. Finally, when the contents of assets/styles/app.scss is requested, the bundle swaps the contents of that file
with the contents of `var/sass/app.built.css`. Nice!

## Deploying

When you deploy, run `sass:build` command before the `asset-map:compile` command so the built file is available:
```shell
php bin/console sass:build
php bin/console asset-map:compile
```

## Configuration

To see the full config from this bundle, run:
```shell
php bin/console config:dump symfonycasts_sass
```
The main option is `root_sass` option, which default to `assets/styles/app.scss`. This represents the source Sass file.

## Using a different binary

This bundle already installed for you the right binary. However if you already have a binary installed on your machine
you can instruct the bundle to use that binary, set the `binary` option:
```yaml
symfonycasts_sass:
    binary: 'node_modules/.bin/sass'
```
