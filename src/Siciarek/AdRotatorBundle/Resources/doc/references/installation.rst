Installation
============

SiciarekAdRotatorBundle can be installed at any moment during a project's lifecycle,
whether it's a clean Symfony2 installation or an existing project.


Downloading the code
--------------------

Use composer to manage your dependencies and download SiciarekAdRotatorBundle:

.. code-block:: bash

    php composer.phar require siciarek/ad-rotator-bundle

You'll be asked to type in a version constraint. 'dev-master' will get you the latest
version, compatible with the latest Symfony2 version.


Enabling SiciarekAdRotatorBundle and its dependencies
-----------------------------------------------------

SiciarekAdRotatorBundle relies on other bundles to implement some features.
Besides the storage layer mentioned on step 2, there are other bundles needed
for SiciarekAdRotatorBundle to work:

    - `SonataBlockBundle <http://sonata-project.org/bundles/block/master/doc/reference/installation.html>`_
    - `SonatajQueryBundle <https://github.com/sonata-project/SonatajQueryBundle/blob/documentation/Resources/doc/reference/installation.rst>`_
    - `KnpMenuBundle <https://github.com/KnpLabs/KnpMenuBundle/blob/master/Resources/doc/index.md#installation>`_ (Version 1.1.*)

These bundles are automatically downloaded by composer as a dependency of SiciarekAdRotatorBundle.
However, you have to enable them in your AppKernel.php, and configure them manually. Don't
forget to enable SiciarekAdRotatorBundle too:

.. code-block:: php

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...

            // Add your dependencies
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),

            // If you haven't already, add the storage bundle
            // This example uses SonataDoctrineORMAdmin but
            // it works the same with the alternatives
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),

            // Then add SiciarekAdRotatorBundle
            new Siciarek\AdRotatorBundle\SiciarekAdRotatorBundle(),
            // ...
        );
    }


If a dependency is already enabled somewhere in your AppKernel.php, you don't need to enable it again.


Configuring SiciarekAdRotatorBundle dependencies
------------------------------------------------

You will need to configure SiciarekAdRotatorBundle's dependencies. For each of the above
mentioned bundles, check their respective installation/configuration instructions
files to see what changes you have to make to your Symfony2 configuration.

In ``app/config/config.yml`` into section ``imports`` add:

.. code-block::	yaml

    - { resource: ../../src/Siciarek/AdRotatorBundle/Resources/config/config.yml }

and as a separate sections add (if you have not done it yet):

.. code-block::	yaml

    stof_doctrine_extensions:
        orm:
            default:
                sluggable: true
                timestampable: true

uncomment:

.. code-block::	yaml

    translator:      { fallback: %locale% }

Setting up routes
-----------------

In ``app/config/routing.yml`` add:

.. code-block::	yaml

    siciarek_ad_rotator:
        resource: "@SiciarekAdRotatorBundle/Controller/"
        type:     annotation
        prefix:   /

Setting up database
-------------------

To update your project's database, run following command

.. code-block:: bash

    php app/console doctrine:schema:update --force


To insert some test data into you project's database, run following command

.. code-block:: bash

    php app/console doctrine:schema:update --force

Cleaning up
-----------

Now, install the assets from the bundles:

.. code-block:: bash

    php app/console assets:install web

Usually, when installing new bundles, it's good practice to also delete your cache:

.. code-block:: bash

    php app/console cache:clear

At this point, you should be able to use ``SiciarekAdRotatorBundle``, administration panel should be visible on page:

http://yourprojectdomain.tld/admin/dashboard
