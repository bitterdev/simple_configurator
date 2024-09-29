<?php

defined('C5_EXECUTE') or die('Access denied');

use Bitter\SimpleConfigurator\Entity\Configurator\Question;
use Bitter\SimpleConfigurator\Entity\Configurator\QuestionOption;
use Concrete\Core\Application\Service\FileManager;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Utility\Service\Identifier;
use Concrete\Core\View\View;

/** @var QuestionOption[] $options */
/** @var Question $question */
/** @var array $questionList */

$app = Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var Identifier $idHelper */
/** @noinspection PhpUnhandledExceptionInspection */
$idHelper = $app->make(Identifier::class);
/** @var FileManager $fileManager */
/** @noinspection PhpUnhandledExceptionInspection */
$fileManager = $app->make(FileManager::class);

$selectBoxId = "reference-" . $idHelper->getString();
$wrapperContainerId = "ccm-options-wrapper-" . $idHelper->getString();
$optionsContainerId = "ccm-options-" . $idHelper->getString();
$optionTemplateId = "ccm-option-template-" . $idHelper->getString();
$addOptionButtonId = "ccm-add-option-" . $idHelper->getString();
/** @noinspection PhpUnhandledExceptionInspection */
View::element("dashboard/help_blocktypes", [], "simple_configurator");
?>

<form action="<?php echo Url::to("/ccm/system/dialogs/simple_configurator/configurator/question/submit") ?>"
      data-dialog-form="edit-question" method="post">
    <?php echo $form->hidden('id', $question->getId()); ?>

    <fieldset>
        <legend>
            <?php echo t("General"); ?>
        </legend>

        <div class="form-group">
            <?php echo $form->label('name', t("Name")); ?>
            <?php echo $form->text('name', $question->getName()); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label('description', t("Description")); ?>
            <?php echo $form->textarea('description', $question->getDescription()); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label('tooltip', t("Tooltip")); ?>
            <?php echo $form->textarea('tooltip', $question->getTooltip()); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label('reference', t("Reference")); ?>
            <?php echo $form->select('reference', $questionList, $question->getReference() instanceof Question ? $question->getReference()->getId() : null, ["id" => $selectBoxId]); ?>

            <small class="text-muted">
                <?php echo t("If you wish, you can choose a different question as a reference to use as a basis for price calculation."); ?>
            </small>
        </div>

        <div class="form-group">
            <div class="form-check">
                <?php echo $form->checkbox("isRequired", 1, $question->isRequired(), ["class" => "form-check-control"]); ?>
                <?php echo $form->label("isRequired", t("Is Required"), ["class" => "form-check-label"]); ?>
            </div>
        </div>
    </fieldset>

    <div id="<?php echo $wrapperContainerId; ?>">
        <fieldset>
            <div class="float-start">
                <!--suppress HtmlUnknownTag -->
                <legend>
                    <?php echo t("Options"); ?>
                </legend>
            </div>

            <div class="float-end">
                <a href="javascript:void(0);" id="<?php echo $addOptionButtonId; ?>" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> <?php echo t("Add Option"); ?>
                </a>
            </div>
        </fieldset>

        <div class="clearfix"></div>

        <div id="<?php echo $optionsContainerId; ?>">
            <p class="no-entries-message <?php echo count($options) > 0 ? "d-none" : "" ?>">
                <?php echo t("There are not items yet."); ?>
            </p>

            <table class="table <?php echo count($options) > 0 ? "" : "d-none" ?>">
                <thead>
                <tr>
                    <th>
                        &nbsp;
                    </th>

                    <th>
                        <?php echo t("Value"); ?>
                    </th>

                    <th>
                        <?php echo t("Image"); ?>
                    </th>

                    <th>
                        &nbsp;
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($options as $option) { ?>
                    <tr class="option-entry ui-sortable-handle">
                        <td>
                            <a href="javascript:void(0);" class="move-handler"><i class="fas fa-arrows-alt"></i></a>
                        </td>

                        <td>
                            <?php echo $form->text('value-' . $option->getId(), $option->getValue(), ["name" => "options[" . $option->getId() . "][value]"]); ?>
                        </td>

                        <td>
                            <?php echo $fileManager->image('image-' . $option->getId(), "options[" . $option->getId() . "][image]", t("Choose File"), $option->getImage()); ?>
                        </td>

                        <td>
                            <div class="float-end">
                                <a href="javascript:void(0);" class="delete btn btn-sm btn-danger"
                                   data-confirm-message="<?php echo h(t("Are you sure?")); ?>"
                                   data-confirm-button-label="<?php echo h(t("Delete")); ?>">
                                    <i class="fas fa-trash"></i> <?php echo t("Delete"); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="dialog-buttons">
        <button class="btn btn-secondary float-start" data-dialog-action="cancel">
            <?php echo t('Cancel') ?>
        </button>

        <button type="button" data-dialog-action="submit" class="btn btn-primary float-end">
            <?php echo t('Save') ?>
        </button>
    </div>
</form>

<!--suppress CssUnresolvedCustomProperty, CssUnusedSymbol -->
<style>
    .move-handler {
        cursor: move;
        color: var(--bs-body-color);
    }

    .ui-widget-content a.btn-danger {
        color: var(--bs-btn-color) !important;
    }
</style>

<script id="<?php echo $optionTemplateId; ?>" type="text/template">
    <tr class="option-entry ui-sortable-handle">
        <td>
            <a href="javascript:void(0);" class="move-handler"><i class="fas fa-arrows-alt"></i></a>
        </td>

        <td>
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" value="<%=value%>" id="value-<%=id%>" name="options[<%=id%>][value]"
                   class="form-control"/>
        </td>

        <td>
            <div id="image-<%=id%>" data-concrete-file-input="image-<%=id%>" class="file-selector">
                <concrete-file-input
                <%=(image !== null ? ":file-id=\"" + image + "\"" : "")%>
                choose-text="<?php echo t("Choose File"); ?>"
                input-name="options[<%=id%>][image]" />
            </div>
        </td>

        <td>
            <a href="javascript:void(0);" class="delete btn btn-sm btn-danger"
               data-confirm-message="<?php echo h(t("Are you sure?")); ?>"
               data-confirm-button-label="<?php echo h(t("Delete")); ?>">
                <i class="fas fa-trash"></i> <?php echo t("Delete"); ?>
            </a>
        </td>
    </tr>
</script>

<!--suppress EqualityComparisonWithCoercionJS, JSUnresolvedVariable, JSUnresolvedFunction -->
<script>
    function generateUUID() {
        let d = new Date().getTime();
        let d2 = ((typeof performance !== 'undefined') && performance.now && (performance.now() * 1000)) || 0;

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            let r = Math.random() * 16;

            if (d > 0) {
                r = (d + r) % 16 | 0;
                d = Math.floor(d / 16);
            } else {
                r = (d2 + r) % 16 | 0;
                d2 = Math.floor(d2 / 16);
            }

            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    (function ($) {
        $(function () {
            $("#<?php echo $selectBoxId; ?>").on("change", function () {
                let hasReference = $("#<?php echo $selectBoxId; ?>").find(":selected").val().length == 36;

                if (hasReference) {
                    $("#<?php echo $wrapperContainerId; ?>").addClass("d-none");
                } else {
                    $("#<?php echo $wrapperContainerId; ?>").removeClass("d-none");
                }
            }).trigger("change");

            let bindOptionEntryEventHandlers = function ($optionItem) {
                $optionItem.find(".delete").on("click", function () {
                    let $el = $(this);
                    ConcreteAlert.confirm(
                        $(this).data("confirmMessage"),
                        function () {
                            $el.closest(".option-entry").remove();
                            $("#ccm-popup-confirmation").remove();
                            updateNoMessageText();
                        },
                        'btn-danger',
                        $(this).data("confirmButtonLabel")
                    );
                });
            };

            let makeOptionsSortable = function () {
                $("#<?php echo $optionsContainerId; ?> tbody").sortable({
                    cursor: 'row-resize',
                    placeholder: 'ui-state-highlight',
                    opacity: '0.55',
                    handle: '.move-handler'
                });
            }

            let updateNoMessageText = function () {
                let hasEntries = $("#<?php echo $optionsContainerId; ?>").find(".option-entry").length > 0;

                if (hasEntries) {
                    $("#<?php echo $optionsContainerId; ?>").find(".table").removeClass("d-none");
                    $("#<?php echo $optionsContainerId; ?>").find(".no-entries-message").addClass("d-none");
                } else {
                    $("#<?php echo $optionsContainerId; ?>").find(".table").addClass("d-none");
                    $("#<?php echo $optionsContainerId; ?>").find(".no-entries-message").removeClass("d-none");
                }
            }

            $(".option-entry").each(function () {
                bindOptionEntryEventHandlers($(this));
            });

            $("#<?php echo $addOptionButtonId; ?>").on("click", function () {
                let $item = $(_.template($("#<?php echo $optionTemplateId; ?>").html())({
                    id: generateUUID(),
                    value: 0,
                    image: 0
                }));

                bindOptionEntryEventHandlers($item);

                Concrete.Vue.activateContext('cms', function (Vue, config) {
                    $item.find(".file-selector").each(function () {
                        new Vue({
                            el: this,
                            components: config.components
                        });
                    });
                });

                $("#<?php echo $optionsContainerId; ?> tbody").append($item);

                updateNoMessageText();

                // update sortable
                $("#<?php echo $optionsContainerId; ?> tbody").sortable('refresh')
            });

            makeOptionsSortable();
        });
    })(jQuery);
</script>