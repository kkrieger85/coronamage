<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit49c1f909f8672165ac72fea6493f7b9e
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Component\\Debug\\' => 24,
            'Symfony\\Component\\Console\\' => 26,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'J' => 
        array (
            'JsonSchema\\' => 11,
        ),
        'E' => 
        array (
            'Eloquent\\Enumeration\\' => 21,
            'Eloquent\\Composer\\Configuration\\' => 32,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Component\\Debug\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/debug',
        ),
        'Symfony\\Component\\Console\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/console',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'JsonSchema\\' => 
        array (
            0 => __DIR__ . '/..' . '/justinrainbow/json-schema/src/JsonSchema',
        ),
        'Eloquent\\Enumeration\\' => 
        array (
            0 => __DIR__ . '/..' . '/eloquent/enumeration/src',
        ),
        'Eloquent\\Composer\\Configuration\\' => 
        array (
            0 => __DIR__ . '/..' . '/eloquent/composer-config-reader/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'MagentoHackathon\\Composer' => 
            array (
                0 => __DIR__ . '/..' . '/magento-hackathon/magento-composer-installer/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit49c1f909f8672165ac72fea6493f7b9e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit49c1f909f8672165ac72fea6493f7b9e::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit49c1f909f8672165ac72fea6493f7b9e::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
