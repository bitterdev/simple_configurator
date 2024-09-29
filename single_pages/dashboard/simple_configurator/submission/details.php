<?php

use Bitter\SimpleConfigurator\Entity\Configurator\Submission;
use Concrete\Core\View\View;

defined('C5_EXECUTE') or die('Access Denied.');

/** @var Submission $submission */

?>

    <div class="ccm-dashboard-header-buttons">
        <?php
        /** @noinspection PhpUnhandledExceptionInspection */
        View::element("dashboard/help", [], "simple_configurator");
        ?>
    </div>

<?php
echo $submission->getAttributesTable();
echo $submission->getPriceTable();