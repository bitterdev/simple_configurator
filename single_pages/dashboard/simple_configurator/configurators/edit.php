<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Bitter\SimpleConfigurator\Entity\Configurator;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\View\View;

/** @var Configurator $configurator */
/** @var Configurator\Step[] $steps */
/** @var array $locales */

$app = Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var Token $token */
/** @noinspection PhpUnhandledExceptionInspection */
$token = $app->make(Token::class);
?>

<div class="ccm-dashboard-header-buttons">
    <div class="btn-group" role="group">
        <?php
        /** @noinspection PhpUnhandledExceptionInspection */
        View::element("dashboard/help", [], "simple_configurator");
        ?>

        <a href="<?php echo Url::to(Page::getCurrentPage(), "add_step", $configurator->getId()) ?>"
           class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo t("Add Step"); ?>
        </a>
    </div>
</div>

<form action="<?php echo (string)Url::to(Page::getCurrentPage(), "edit", $configurator->getId()); ?>" method="post">
    <?php echo $token->output("update_configurator"); ?>

    <fieldset>
        <legend>
            <?php echo t("General"); ?>
        </legend>

        <div class="form-group">
            <?php echo $form->label("configuratorName", t("Name")); ?>
            <?php echo $form->text("configuratorName", $configurator->getName()); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label("locale", t("Locale")); ?>
            <?php echo $form->select("locale", $locales, $configurator->getLocale()); ?>
        </div>
    </fieldset>

    <fieldset>
        <legend>
            <?php echo t("Steps"); ?>
        </legend>

        <?php if (count($steps) > 0) { ?>
            <table class="table" id="ccm-steps-table">
                <thead>
                <tr>
                    <th>
                        &nbsp;
                    </th>

                    <th>
                        <?php echo t("Name"); ?>
                    </th>

                    <th>
                        &nbsp;
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($steps as $step) { ?>
                    <tr class="ui-sortable-handle" data-step-id="<?php echo h($step->getId()); ?>">
                        <td>
                            <a href="javascript:void(0);" class="move-handler"><i class="fas fa-arrows-alt"></i></a>
                        </td>

                        <td>
                            <?php echo $step->getDisplayName(); ?>
                        </td>

                        <td>
                            <div class="float-end">
                                <a href="<?php echo Url::to(Page::getCurrentPage(), "remove_step", $step->getId()); ?>"
                                   class="delete btn btn-sm btn-danger"
                                   data-confirm-message="<?php echo h(t("Are you sure?")); ?>"
                                   data-confirm-button-label="<?php echo h(t("Delete")); ?>">
                                    <i class="fas fa-trash"></i> <?php echo t("Delete"); ?>
                                </a>

                                <a href="<?php echo Url::to(Page::getCurrentPage(), "edit_step", $step->getId()); ?>"
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
                .move-handler {
                    cursor: move;
                    color: var(--bs-body-color);
                }

                .table tbody td {
                    line-height: 40px;
                }
            </style>

            <!--suppress JSUnresolvedVariable -->
            <script>
                (function ($) {
                    $(function () {
                        $("#ccm-steps-table tbody").sortable({
                            cursor: 'row-resize',
                            placeholder: 'ui-state-highlight',
                            opacity: '0.55',
                            handle: '.move-handler',
                            stop: function () {
                                let updatedSortOrder = [];
                                let i = 0;

                                $("#ccm-steps-table tbody tr").each(function () {
                                    let stepId = $(this).data("stepId");

                                    i++;

                                    updatedSortOrder.push({
                                        id: stepId,
                                        sortIndex: i
                                    });
                                });

                                $.ajax({
                                    type: "POST",
                                    url: <?php echo json_encode((string)Url::to(Page::getCurrentPage(), "update_sort_order")); ?>,
                                    data: {
                                        updatedSortOrder: updatedSortOrder
                                    },
                                    success: function (json) {
                                        if (json.error) {
                                            for (let errorMessage of json.errors) {
                                                ConcreteAlert.error({
                                                    message: errorMessage
                                                });
                                            }
                                        } else {
                                            ConcreteAlert.notify(json);
                                        }
                                    },
                                    dataType: "json"
                                });
                            }
                        }).disableSelection();

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
    </fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?php echo Url::to("/dashboard/simple_configurator/configurators"); ?>" class="btn btn-secondary">
                <?php echo t("Back"); ?>
            </a>

            <button type="submit" class="btn btn-primary float-end">
                <i class="fas fa-save"></i> <?php echo t("Save") ?>
            </button>
        </div>
    </div>
</form>