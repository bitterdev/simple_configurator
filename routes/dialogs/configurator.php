<?php

/** @noinspection DuplicatedCode */

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Routing\Router;

/**
 * @var Router $router
 * Base path: /ccm/system/dialogs/simple_configurator/configurator
 * Namespace: Concrete\Package\SimpleConfigurator\Controller\Dialog\Configurator
 */

$router->all('/question/add/{stepId}', 'Question::add');
$router->all('/question/edit/{questionId}', 'Question::edit');
$router->all('/question/edit_prices/{questionId}', 'Question::editPrices');
$router->all('/question/remove/{questionId}', 'Question::remove');
$router->all('/question/submit', 'Question::submit');
$router->all('/question/submit_prices', 'Question::submitPrices');