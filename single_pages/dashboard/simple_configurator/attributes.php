<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Controller\Element\Attribute\KeyList;
use Concrete\Core\View\View;

?>

<div class="ccm-dashboard-header-buttons">
    <?php
    /** @noinspection PhpUnhandledExceptionInspection */
    View::element("dashboard/help", [], "simple_configurator");
    ?>
</div>

<?php
/** @var KeyList $attributeView */
/** @noinspection PhpDeprecationInspection */
$attributeView->render();
