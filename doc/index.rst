Sass For Symfony!
=================

This bundle makes it easy to use Sass with Symfony's AssetMapper Component
(no Node required!).

- Automatically downloads the correct Sass binary
- Adds a ``sass:build`` command to build and watch your Sass changes

Installation
------------

Install the bundle:

.. code-block:: terminal

    $ composer require symfonycasts/sass-bundle

Usage
-----

Start by writing your first Sass file ``assets/styles/app.scss``, and let's add some basic style

.. code-block:: scss

    /* assets/styles/app.scss */

    $red: #fc030b;

    body {
      background: $red;
    }

Then point your styles in your template

.. code-block:: html+twig

    {# templates/base.html.twig #}

    {% block stylesheets %}
        <link rel="stylesheet" href="{{ asset('styles/app.scss') }}">
    {% endblock %}

That's right! You point directly to the ``.scss`` file. But don't worry, the final built ``.css`` file will be returned!

Then run the command:

.. code-block:: terminal

    $ php bin/console sass:build --watch

And that's it!

How Does it Work?
-----------------

The first time you run one of the Sass commands, the bundle will download the correct Sass binary for your system into the ``bin/dart-sass`` directory.

When you run ``sass:build``, that binary is used to compile Sass files into a ``var/sass/app.built.css`` file. Finally, when the contents of ``assets/styles/app.scss`` are requested, the bundle swaps the contents of that file with the contents of ``var/sass/app.built.css``. Nice!

Excluding Sass Files from AssetMapper
-------------------------------------

Because you have ``.scss`` files in your ``assets/`` directory, when you deploy, these
source files will be copied into the ``public/assets/`` directory. To prevent that,
you can exclude them from asset mapper:

.. code-block:: yaml
    # config/packages/asset_mapper.yaml
    framework:
        asset_mapper:
            paths:
                - assets/
            excluded_patterns:
                - '*/assets/styles/_*.scss'
                - '*/assets/styles/**/_*.scss'

Note: be sure not to exclude your *main* SCSS file (e.g. ``assets/styles/app.scss``):
this *is* used in AssetMapper and its contents are swapped for the final, built CSS.

Using Bootstrap Sass
--------------------

`Bootstrap <https://getbootstrap.com/>`_ is available as Sass, allowing you to customize the look and feel of your app. An easy way to get the source Sass files is via a Composer package:

.. code-block:: terminal

    $ composer require twbs/bootstrap

Now, import the core ``bootstrap.scss`` from your ``app.scss`` file:

.. code-block:: scss

    /* Override some Bootstrap variables */
    $red: #FB4040;

    @import '../../vendor/twbs/bootstrap/scss/bootstrap';

Deploying
----------

When you deploy, run ``sass:build`` command before the ``asset-map:compile`` command so the built file is available:

.. code-block:: terminal

    $ php bin/console sass:build
    $ php bin/console asset-map:compile

Limitation: ``url()`` Relative Paths
------------------------------------

When using ``url()`` inside a Sass file, currently, the path must be relative to the *root* ``.scss`` file. For example, suppose the root ``.scss`` file is:

.. code-block:: scss

    /* assets/styles/app.scss */
    import 'tools/base';

Assume there is an ``assets/images/login-bg.png`` file that you want to refer to from ``base.css``:

.. code-block:: scss

    /* assets/styles/tools/base.scss */
    .splash {
        /* This SHOULD work, but doesn't */
        background-image: url('../../images/login-bg.png');

        /* This DOES work: it's relative to app.scss */
        background-image: url('../images/login-bg.png');
    }

It should be possible to use ``url()`` with a path relative to the current file. However, that is not currently possible. See `this issue <https://github.com/SymfonyCasts/sass-bundle/issues/2>`_ for more details.

Configuration
--------------

To see the full config from this bundle, run:

.. code-block:: terminal

    $ php bin/console config:dump symfonycasts_sass

The main option is ``root_sass`` option, which defaults to ``assets/styles/app.scss``. This represents the source Sass file.

Using a different binary
--------------------------

This bundle already installed for you the right binary. However, if you already have a binary installed on your machine you can instruct the bundle to use that binary, set the ``binary`` option:

.. code-block:: yaml

    symfonycasts_sass:
        binary: 'node_modules/.bin/sass'
