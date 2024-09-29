<?php

namespace Concrete\Package\SimpleConfigurator\Controller\SinglePage\Dashboard\SimpleConfigurator;

use Bitter\SimpleConfigurator\Attribute\Key\SubmissionKey;
use Concrete\Core\Attribute\CategoryObjectInterface;
use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Attribute\TypeFactory;
use Concrete\Core\Page\Controller\DashboardAttributesPageController;
use Concrete\Core\Support\Facade\Url;

class Attributes extends DashboardAttributesPageController
{
    public function view()
    {
        $this->renderList();
    }

    public function edit($akID = null)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->renderEdit(SubmissionKey::getByID($akID), Url::to('/dashboard/simple_configurator/attributes', 'view'));
    }

    public function update($akID = null)
    {
        $this->edit($akID);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->executeUpdate(SubmissionKey::getByID($akID), Url::to('/dashboard/simple_configurator/attributes', 'view'));
    }

    public function select_type($type = null)
    {
        /** @var TypeFactory $typeFactory */
        /** @noinspection PhpUnhandledExceptionInspection */
        $typeFactory = $this->app->make(TypeFactory::class);
        $this->renderAdd($typeFactory->getByID($type), Url::to('/dashboard/simple_configurator/attributes', 'view'));
    }

    public function add($type = null)
    {
        $this->select_type($type);
        /** @var TypeFactory $typeFactory */
        /** @noinspection PhpUnhandledExceptionInspection */
        $typeFactory = $this->app->make(TypeFactory::class);
        $this->executeAdd($typeFactory->getByID($type), Url::to('/dashboard/simple_configurator/attributes', 'view'));
    }


    public function delete($akID = null)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->executeDelete(SubmissionKey::getByID($akID), Url::to('/dashboard/simple_configurator/attributes', 'view'));
    }

    protected function getCategoryObject(): CategoryObjectInterface
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Category::getByHandle('configurator');
    }
}
