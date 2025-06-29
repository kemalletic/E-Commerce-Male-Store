<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita147a5ab1b7ae84b61528eae9bf2caa9
{
    public static $files = array (
        'fc73bab8d04e21bcdda37ca319c63800' => __DIR__ . '/..' . '/mikecao/flight/flight/autoload.php',
    );

    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/backend',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita147a5ab1b7ae84b61528eae9bf2caa9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita147a5ab1b7ae84b61528eae9bf2caa9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita147a5ab1b7ae84b61528eae9bf2caa9::$classMap;

        }, null, ClassLoader::class);
    }
}
