<?php

class Cmproduct extends Product
{

    public static function flushCache()
    {
        parent::flushPriceCache();
        parent::$_frontFeaturesCache = array();
        parent::$producPropertiesCache = array();
    }

}