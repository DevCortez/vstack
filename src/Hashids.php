<?php

namespace marcusvbda\vstack;

use Hashids\Hashids as Hids;

class Hashids
{
    public static function encode($value)
    {
        return (static::getInstance())->encode($value);
    }

    public static function decode($value)
    {
        return (static::getInstance())->decode($value);
    }

    public static function getConfigs()
    {
        $connection = config("hashids.default");
        return config("hashids.connections.$connection");
    }

    public static function getInstance()
    {
        $config = static::getConfigs();
        $salt = data_geT($config, "salt");
        $length = data_geT($config, "length");
        $alphabet = data_geT($config, "alphabet");
        return new Hids($salt, $length, $alphabet);
    }
}
