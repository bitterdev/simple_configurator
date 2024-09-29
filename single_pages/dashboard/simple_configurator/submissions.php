<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Bitter\SimpleConfigurator\Entity\Attribute\Key\SubmissionKey;
use Bitter\SimpleConfigurator\Entity\Configurator\Submission;
use Concrete\Core\Localization\Service\Date;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\View\View;

/** @var $submissions Submission[] */
/** @var SubmissionKey[] $attributes */

$app = Application::getFacadeApplication();
/** @var Date $dateService */
$dateService = $app->make(Date::class);

?>

<div class="ccm-dashboard-header-buttons">
    <?php
    /** @noinspection PhpUnhandledExceptionInspection */
    View::element("dashboard/help", [], "simple_configurator");
    ?>
</div>

<?php if (count($submissions) > 0) { ?>
    <table class="table" id="ccm-submissions-table">
        <thead>
        <tr>
            <th>
                <?php echo t("Created At"); ?>
            </th>

            <?php foreach ($attributes as $attribute) { ?>
                <th>
                    <?php echo $attribute->getAttributeKeyName(); ?>
                </th>
            <?php } ?>

            <th>
                &nbsp;
            </th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($submissions as $submission) { ?>
            <tr>
                <td>
                    <?php echo $dateService->formatDateTime($submission->getCreatedAt()); ?>
                </td>

                <?php foreach ($attributes as $attribute) { ?>
                    <td>
                        <?php echo (string)$submission->getAttribute($attribute->getAttributeKeyHandle()); ?>
                    </td>
                <?php } ?>

                <td>
                    <div class="float-end">
                        <a href="<?php echo Url::to(Page::getCurrentPage(), "remove", $submission->getId()); ?>"
                           class="edit btn btn-sm btn-danger">
                            <?php echo t("Remove"); ?>
                        </a>

                        <a href="<?php echo Url::to(Page::getCurrentPage(), "export", $submission->getId()); ?>"
                           class="edit btn btn-sm btn-secondary">
                            <?php echo t("Export"); ?>
                        </a>

                        <a href="<?php echo Url::to(Page::getCurrentPage(), "details", $submission->getId()); ?>"
                           class="edit btn btn-sm btn-secondary">
                            <?php echo t("Details"); ?>
                        </a>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <p>
        <?php echo t("There are not items yet."); ?>
    </p>
<?php } ?>