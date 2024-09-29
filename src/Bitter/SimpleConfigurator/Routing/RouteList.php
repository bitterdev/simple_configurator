<?php

namespace Bitter\SimpleConfigurator\Routing;

use Bitter\SimpleConfigurator\API\V1\Middleware\FractalNegotiatorMiddleware;
use Bitter\SimpleConfigurator\API\V1\Configurator;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {
        $router
            ->buildGroup()
            ->setPrefix('/api/v1')
            ->addMiddleware(FractalNegotiatorMiddleware::class)
            ->routes(function ($groupRouter) {
                /** @var $groupRouter Router */
                /** @noinspection PhpParamsInspection */
                $groupRouter->all('/configurator/get_steps', [Configurator::class, 'getSteps']);
                /** @noinspection PhpParamsInspection */
                $groupRouter->all('/configurator/get_questions/{stepId}', [Configurator::class, 'getQuestions']);
            });

        $router
            ->buildGroup()
            ->setNamespace('Concrete\Package\SimpleConfigurator\Controller\Dialog\Support')
            ->setPrefix('/ccm/system/dialogs/simple_configurator')
            ->routes('dialogs/support.php', 'simple_configurator');

        $router
            ->buildGroup()
            ->setNamespace('Concrete\Package\SimpleConfigurator\Controller\Dialog\Configurator')
            ->setPrefix('/ccm/system/dialogs/simple_configurator/configurator')
            ->routes('dialogs/configurator.php', 'simple_configurator');
    }
}