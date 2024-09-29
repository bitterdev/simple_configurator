<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Application\Service\FileManager;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\View\View;

/** @var int $shippingCostsFile */
/** @var string $notificationMailAddress */
/** @var array $currencySymbolPositions */
/** @var string $currencySymbol */
/** @var string $currencyCode */
/** @var string $currencySymbolPosition */
/** @var int $currencySymbolSpaces */
/** @var int $decimals */
/** @var string $decimalPoint */
/** @var string $thousandsSeparator */

$app = Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var Token $token */
/** @noinspection PhpUnhandledExceptionInspection */
$token = $app->make(Token::class);
/** @var FileManager $fileManager */
/** @noinspection PhpUnhandledExceptionInspection */
$fileManager = $app->make(FileManager::class);

?>

<div class="ccm-dashboard-header-buttons">
    <?php
    /** @noinspection PhpUnhandledExceptionInspection */
    View::element("dashboard/help", [], "simple_configurator");
    ?>
</div>

<form action="#" method="post">
    <?php echo $token->output("update_settings"); ?>

    <fieldset>
        <legend>
            <?php echo t("General"); ?>
        </legend>

        <div class="form-group">
            <?php echo $form->label("notificationMailAddress", t("Notification Mail Address")); ?>
            <?php echo $form->text("notificationMailAddress", $notificationMailAddress); ?>
        </div>
    </fieldset>

    <fieldset>
        <legend>
            <?php echo t("Money Format"); ?>
        </legend>

        <div class="form-group">
            <?php echo $form->label("currencySymbol", t("Currency Symbol")); ?>
            <?php echo $form->text("currencySymbol", $currencySymbol); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label("currencyCode", t("Currency Code")); ?>
            <?php echo $form->text("currencyCode", $currencyCode); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label("currencySymbolPosition", t("Currency Symbol Position")); ?>
            <?php echo $form->select("currencySymbolPosition", $currencySymbolPositions, $currencySymbolPosition); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label("currencySymbolSpaces", t("Currency Symbol Space Counter")); ?>
            <?php echo $form->number("currencySymbolSpaces", $currencySymbolSpaces); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label("decimals", t("Decimals")); ?>
            <?php echo $form->number("decimals", $decimals); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label("decimalPoint", t("Decimal Point")); ?>
            <?php echo $form->text("decimalPoint", $decimalPoint); ?>
        </div>

        <div class="form-group">
            <?php echo $form->label("thousandsSeparator", t("Thousands Separator")); ?>
            <?php echo $form->text("thousandsSeparator", $thousandsSeparator); ?>
        </div>
    </fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <?php echo $form->submit('save', t('Save'), ['class' => 'btn btn-primary float-end']); ?>
        </div>
    </div>
</form>
