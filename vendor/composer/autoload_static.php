<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0291aadf1196b1597287178197598409
{
    public static $prefixLengthsPsr4 = array (
        'h' => 
        array (
            'hxs9712\\wechathxs\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'hxs9712\\wechathxs\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0291aadf1196b1597287178197598409::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0291aadf1196b1597287178197598409::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0291aadf1196b1597287178197598409::$classMap;

        }, null, ClassLoader::class);
    }
}
