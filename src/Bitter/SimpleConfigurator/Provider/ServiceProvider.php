<?php

namespace Bitter\SimpleConfigurator\Provider;

use Bitter\SimpleConfigurator\Attribute\Category\Manager;
use Concrete\Core\Application\Application;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Foundation\Service\Provider;
use Concrete\Core\Routing\RouterInterface;
use Doctrine\DBAL\Types\Type;
use Bitter\SimpleConfigurator\Attribute\Key\SubmissionKey;
use Bitter\SimpleConfigurator\Backup\ContentImporter\Importer\Routine\ImportConfiguratorRoutine;
use Bitter\SimpleConfigurator\Routing\RouteList;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Illuminate\Contracts\Container\BindingResolutionException;
use ReflectionException;

class ServiceProvider extends Provider
{
    protected RouterInterface $router;

    public function __construct(
        Application     $app,
        RouterInterface $router
    )
    {
        parent::__construct($app);

        $this->router = $router;
    }

    private function overrideAttributeCategoryManager()
    {
        $this->app->singleton('manager/attribute/category', function ($app) {
            return new Manager($app);
        });
    }

    public function register()
    {
        $this->registerDoctrineTypes();
        $this->registerRoutes();
        $this->overrideAttributeCategoryManager();
        $this->applyCoreFixes();
        $this->addImporterRoutines();
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->overrideDiscriminatorMap();
    }

    /**
     * @throws MappingException
     * @throws \Doctrine\Persistence\Mapping\MappingException
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    private function overrideDiscriminatorMap(): void
    {
        /** @var EntityManagerInterface $entityManager */
        /** @noinspection PhpUnhandledExceptionInspection */
        $entityManager = $this->app->make(EntityManagerInterface::class);

        $metaData = $entityManager->getMetadataFactory()->getMetadataFor(Key::class);

        $metaData->addDiscriminatorMapClass("submissionkey", \Bitter\SimpleConfigurator\Entity\Attribute\Key\SubmissionKey::class);

        $entityManager->getMetadataFactory()->setMetadataFor(Key::class, $metaData);
    }

    protected function addImporterRoutines()
    {
        /** @var \Concrete\Core\Backup\ContentImporter\Importer\Manager $importer */
        /** @noinspection PhpUnhandledExceptionInspection */
        $importer = $this->app->make('import/item/manager');
        /** @noinspection PhpUnhandledExceptionInspection */
        $importer->registerImporterRoutine($this->app->make(ImportConfiguratorRoutine::class));
    }

    private function applyCoreFixes()
    {
        $this->app->bind(
            '\Concrete\Package\SimpleConfigurator\Attribute\Key\SubmissionKey',
            SubmissionKey::class
        );
    }

    private function registerRoutes()
    {
        $this->router->loadRouteList(new RouteList());
    }

    private function registerDoctrineTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        Type::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');
    }
}