<?php /** @noinspection DuplicatedCode */

defined('C5_EXECUTE') or die('Access Denied.');

use Bitter\SimpleConfigurator\Entity\Configurator;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\View\View;

/** @var Configurator[] $configurators */
?>

    <div class="ccm-dashboard-header-buttons">
        <div class="btn-group" role="group">
            <?php
            /** @noinspection PhpUnhandledExceptionInspection */
            View::element("dashboard/help", [], "simple_configurator");
            ?>

            <a href="<?php echo Url::to(Page::getCurrentPage(), "add") ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?php echo t("Add Configurator"); ?>
            </a>
        </div>
    </div>

<?php if (count($configurators) > 0) { ?>
    <table class="table" id="ccm-configurators-table">
        <thead>
        <tr>
            <th>
                <?php echo t("Name"); ?>
            </th>

            <th>
                &nbsp;
            </th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($configurators as $configurator) { ?>
            <tr data-configurator-id="<?php echo h($configurator->getId()); ?>">
                <td>
                    <?php echo $configurator->getDisplayName(); ?>
                </td>

                <td>
                    <div class="float-end">
                        <a href="<?php echo Url::to(Page::getCurrentPage(), "remove", $configurator->getId()); ?>"
                           class="delete btn btn-sm btn-danger"
                           data-confirm-message="<?php echo h(t("Are you sure?")); ?>"
                           data-confirm-button-label="<?php echo h(t("Delete")); ?>">
                            <i class="fas fa-trash"></i> <?php echo t("Delete"); ?>
                        </a>

                        <a href="<?php echo Url::to(Page::getCurrentPage(), "edit", $configurator->getId()); ?>"
                           class="edit btn btn-sm btn-secondary">
                            <i class="fas fa-pencil-alt"></i> <?php echo t("Edit"); ?>
                        </a>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <!--suppress CssUnresolvedCustomProperty -->
    <style>
        .table tbody td {
            line-height: 40px;
        }
    </style>

    <!--suppress JSUnresolvedVariable -->
    <script>
        (function ($) {
            $(function () {
                $(".delete").on("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    let actionUrl = $(this).attr("href");

                    ConcreteAlert.confirm(
                        $(this).data("confirmMessage"),
                        function () {
                            window.location.href = actionUrl;
                        },
                        'btn-danger',
                        $(this).data("confirmButtonLabel")
                    );

                    return false;
                });
            });
        })(jQuery);
    </script>
<?php } else { ?>
    <p>
        <?php echo t("There are not items yet."); ?>
    </p>
<?php } ?>