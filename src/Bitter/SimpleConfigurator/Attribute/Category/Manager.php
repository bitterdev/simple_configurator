<?php /** @noinspection PhpUnused */

namespace Bitter\SimpleConfigurator\Attribute\Category;

use Concrete\Core\Attribute\Category\Manager as CoreManager;

class Manager extends CoreManager
{
    public function createConfiguratorDriver()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->app->make(ConfiguratorCategory::class);
    }
}
