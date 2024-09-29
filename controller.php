<?php /** @noinspection PhpDeprecationInspection */

namespace Concrete\Package\SimpleConfigurator;

use Bitter\SimpleConfigurator\Provider\ServiceProvider;
use Concrete\Core\Backup\ContentImporter;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Concrete\Core\Package\Package;
use Doctrine\DBAL\Exception;

class Controller extends Package implements ProviderAggregateInterface
{
    protected string $pkgHandle = 'simple_configurator';
    protected string $pkgVersion = '0.0.2';
    protected $appVersionRequired = '9.0.0';
    protected $pkgAutoloaderRegistries = [
        'src/Bitter/SimpleConfigurator' => 'Bitter\SimpleConfigurator',
        'src/Bitter/SimpleConfigurator/Attribute' => 'Concrete\Package\SimpleConfigurator\Attribute'
    ];

    public function getPackageDescription(): string
    {
        return t('The Simple Configurator add-On for Concrete CMS lets you add multi-step forms to your site.');
    }

    public function getPackageName(): string
    {
        return t('Simple Configurator');
    }

    public function getEntityManagerProvider(): StandardPackageProvider
    {
        $locations = [
            'src/Bitter/SimpleConfigurator/Entity' => 'Bitter\SimpleConfigurator\Entity'
        ];

        return new StandardPackageProvider($this->app, $this, $locations);
    }

    public function on_start()
    {
        $autoloadFile = $this->getPackagePath() . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

        if (file_exists($autoloadFile)) {
            include($autoloadFile);
        }

        /** @var ServiceProvider $serviceProvider */
        /** @noinspection PhpUnhandledExceptionInspection */
        $serviceProvider = $this->app->make(ServiceProvider::class);
        $serviceProvider->register();
    }

    public function install(): \Concrete\Core\Entity\Package
    {
        $this->on_start();
        $pkg = parent::install();
        $this->installContentFile("data.xml");
        $this->installConfiguratorContent();
        return $pkg;
    }

    private function installConfiguratorContent()
    {
        $hasContentAvailable = false;

        /** @var Connection $db */
        /** @noinspection PhpUnhandledExceptionInspection */
        $db = $this->app->make(Connection::class);

        try {
            /** @noinspection SqlDialectInspection */
            /** @noinspection SqlNoDataSourceInspection */
            $hasContentAvailable = (int)$db->fetchColumn("SELECT COUNT(*) FROM AquagreenConfiguratorStep") > 0;
        } catch (Exception) {
            // Ignore
        }

        if (!$hasContentAvailable) {
            $cf = new ContentImporter();

            try {
                $cf->importFiles($this->getPackagePath() . DIRECTORY_SEPARATOR . "configurator_files", false);
            } catch (\Exception) {
            }

            $cf->importContentFile($this->getPackagePath() . DIRECTORY_SEPARATOR . "configurator_content.xml");
        }
    }

    public function upgrade()
    {
        parent::upgrade();
        $this->installContentFile("data.xml");
        $this->installConfiguratorContent();
    }

    public function uninstall()
    {

        /** @var Connection $db */
        /** @noinspection PhpUnhandledExceptionInspection */
        $db = $this->app->make(Connection::class);

        /** @noinspection SqlDialectInspection */
        /** @noinspection SqlNoDataSourceInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        $db->executeQuery("SET FOREIGN_KEY_CHECKS = 0");

        parent::uninstall();
    }

}
