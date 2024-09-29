<?php

namespace Bitter\SimpleConfigurator\Attribute\Key;

use Bitter\SimpleConfigurator\Attribute\Category\ConfiguratorCategory;
use Concrete\Core\Support\Facade\Facade;

class SubmissionKey extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ConfiguratorCategory::class;
    }

    public static function getByHandle($handle)
    {
        return static::getFacadeRoot()->getAttributeKeyByHandle($handle);
    }

    public static function getByID($akID)
    {
        return static::getFacadeRoot()->getAttributeKeyByID($akID);
    }
}
