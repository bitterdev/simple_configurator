<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Bitter\SimpleConfigurator\Entity\Configurator;
use Bitter\SimpleConfigurator\Entity\Configurator\Step;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\View\View;

/** @var Step $step */
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

        <a href="javascript:void(0);" class="btn btn-primary" id="ccm-add-question"
           data-dialog-url="<?php echo Url::to("/ccm/system/dialogs/simple_configurator/configurator/question/add", $step->getId()); ?>"
           data-dialog-title="<?php echo h(t("Add Question")); ?>">

            <i class="fas fa-plus"></i> <?php echo t("Add Question"); ?>
        </a>
    </div>
</div>

<form action="#" method="post">
    <?php echo $token->output("update_step"); ?>

    <fieldset>
        <legend>
            <?php echo t("General"); ?>
        </legend>

        <div class="form-group">
            <?php echo $form->label("stepName", t("Name")); ?>
            <?php echo $form->text("stepName", $step->getName()); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label("locale", t("Locale")); ?>
            <?php echo $form->select("locale", $locales, $step->getLocale()); ?>
        </div>
    </fieldset>

    <fieldset>
        <legend>
            <?php echo t("Questions"); ?>
        </legend>

        <div id="ccm-question-container" data-step-id="<?php echo h($step->getId()); ?>"></div>
    </fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?php echo Url::to("/dashboard/simple_configurator/configurators/edit", $step->getConfigurator() instanceof Configurator ? $step->getConfigurator()->getId() : null); ?>"
               class="btn btn-secondary">
                <?php echo t("Back"); ?>
            </a>

            <button type="submit" class="btn btn-primary float-end">
                <i class="fas fa-save"></i> <?php echo t("Save") ?>
            </button>
        </div>
    </div>
</form>

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

<script id="ccm-questions-template" type="text/template">
    <% if(questions.length) { %>
    <table class="table">
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
        <% _.each(questions, function(question) { %>
        <tr class="ui-sortable-handle" data-question-id="<%=question.id %>">
            <td>
                <a href="javascript:void(0);" class="move-handler"><i class="fas fa-arrows-alt"></i></a>
            </td>

            <td>
                <%=question.displayName %>
            </td>

            <td>
                <div class="float-end">
                    <a href="javascript:void(0);" class="delete btn btn-sm btn-danger"
                       data-action-url="<?php echo Url::to("/ccm/system/dialogs/simple_configurator/configurator/question/remove"); ?>/<%=question.id %>"
                       data-confirm-message="<?php echo h(t("Are you sure?")); ?>"
                       data-confirm-button-label="<?php echo h(t("Delete")); ?>">
                        <i class="fas fa-trash"></i> <?php echo t("Delete"); ?>
                    </a>

                    <a href="javascript:void(0);" class="edit btn btn-sm btn-secondary"
                       data-dialog-url="<?php echo Url::to("/ccm/system/dialogs/simple_configurator/configurator/question/edit_prices"); ?>/<%=question.id %>"
                       data-dialog-title="<?php echo h(t("Edit Prices")); ?>">
                        <i class="fas fa-dollar-sign"></i> <?php echo t("Edit Prices"); ?>
                    </a>

                    <a href="javascript:void(0);" class="edit btn btn-sm btn-secondary"
                       data-dialog-url="<?php echo Url::to("/ccm/system/dialogs/simple_configurator/configurator/question/edit"); ?>/<%=question.id %>"
                       data-dialog-title="<?php echo h(t("Edit Question")); ?>">
                        <i class="fas fa-pencil-alt"></i> <?php echo t("Edit"); ?>
                    </a>
                </div>
            </td>
        </tr>

        <%=question.name %>
        <% }); %>
        </tbody>
    </table>
    <% } else { %>
    <p>
        <?php echo t("There are not items yet."); ?>
    </p>
    <% } %>
</script>

<!--suppress JSUnresolvedVariable -->
<script>
    (function ($) {
        $(function () {
            $("#ccm-add-question").on("click", function (e) {
                e.preventDefault()
                e.stopPropagation();

                jQuery.fn.dialog.open({
                    href: $(this).data("dialogUrl"),
                    modal: true,
                    width: 700,
                    title: $(this).data("dialogTitle"),
                    height: '80%',
                    close: function () {
                        updateQuestions();
                    }
                });

                return false;
            });

            let makeQuestionsSortable = function () {
                $("#ccm-question-container tbody").sortable({
                    cursor: 'row-resize',
                    placeholder: 'ui-state-highlight',
                    opacity: '0.55',
                    handle: '.move-handler',
                    stop: function () {
                        let updatedSortOrder = [];
                        let i = 0;

                        $("#ccm-question-container tbody tr").each(function () {
                            let questionId = $(this).data("questionId");

                            i++;

                            updatedSortOrder.push({
                                id: questionId,
                                sortIndex: i
                            });
                        });

                        $.ajax({
                            type: "POST",
                            url: <?php echo json_encode((string)Url::to(Page::getCurrentPage(), "update_sort_order", $step->getId())); ?>,
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
            };

            let updateQuestions = function () {
                let $el = $("#ccm-question-container");

                $.getJSON(CCM_DISPATCHER_FILENAME + "/api/v1/configurator/get_questions/" + $el.data("stepId"), function (json) {
                    $el.html(_.template($("#ccm-questions-template").html())({
                        questions: json.questions
                    }));

                    $el.find(".edit").on("click", function (e) {
                        e.stopPropagation();
                        e.preventDefault();

                        jQuery.fn.dialog.open({
                            href: $(this).data("dialogUrl"),
                            modal: true,
                            width: 700,
                            title: $(this).data("dialogTitle"),
                            height: '80%',
                            close: function () {
                                updateQuestions();
                            }
                        });

                        return false;
                    });

                    $el.find(".delete").on("click", function () {
                        let actionUrl = $(this).data("actionUrl");
                        ConcreteAlert.confirm(
                            $(this).data("confirmMessage"),
                            function () {
                                $.getJSON(actionUrl, function (json) {
                                    if (json.error) {
                                        for (let errorMessage of json.errors) {
                                            ConcreteAlert.error({
                                                message: errorMessage
                                            });
                                        }
                                    } else {
                                        ConcreteAlert.notify(json);
                                    }

                                    $('.ui-dialog-content').dialog('close');

                                    updateQuestions();
                                });
                            },
                            'btn-danger',
                            $(this).data("confirmButtonLabel")
                        );
                    });

                    makeQuestionsSortable();
                });
            }
            updateQuestions();
        });
    })(jQuery);
</script>