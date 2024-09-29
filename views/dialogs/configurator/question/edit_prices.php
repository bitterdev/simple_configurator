<?php

defined('C5_EXECUTE') or die('Access denied');

use Bitter\SimpleConfigurator\Entity\Configurator\Question;
use Bitter\SimpleConfigurator\Entity\Configurator\QuestionOption;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Site\Service;
use Concrete\Core\View\View;

/** @var Question $question */
/** @var QuestionOption[] $options */

$app = Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var Service $siteService */
/** @noinspection PhpUnhandledExceptionInspection */
$siteService = $app->make(Service::class);
$config = $siteService->getSite()->getConfigRepository();

$currencySymbol = (string)$config->get("simple_configurator.configurator.money_formatting.currency_symbol", "USD");
/** @noinspection PhpUnhandledExceptionInspection */
View::element("dashboard/help_blocktypes", [], "simple_configurator");
?>

<form action="<?php echo Url::to("/ccm/system/dialogs/simple_configurator/configurator/question/submit_prices") ?>"
      data-dialog-form="edit-question-prices" method="post">

    <?php echo $form->hidden('id', $question->getId()); ?>

    <?php if (count($options) > 0) { ?>
        <table class="table">
            <thead>
            <tr>
                <th>
                    <?php echo t("Value"); ?>
                </th>

                <th>
                    <?php echo t("Price"); ?>
                </th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($options as $option) { ?>
                <tr>
                    <td>
                        <?php echo $option->getValue(); ?>
                    </td>

                    <td>
                        <div class="input-group">
                            <?php echo $form->number('price-' . $option->getId(), $option->getPrice(), [
                                "name" => "options[" . $option->getId() . "][price]",
                                "placeholder" => t("Please enter a price..."),
                                "aria-label" => t("Price"),
                                "aria-describedby" => 'price-addon-' . $option->getId()
                            ]); ?>

                            <span class="input-group-text" id="<?php echo 'price-addon-' . $option->getId(); ?>">
                                <?php echo $currencySymbol; ?>
                            </span>
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

    <div class="dialog-buttons">
        <button class="btn btn-secondary float-start" data-dialog-action="cancel">
            <?php echo t('Cancel') ?>
        </button>

        <button type="button" data-dialog-action="submit" class="btn btn-primary float-end">
            <?php echo t('Save') ?>
        </button>
    </div>
</form>