<?php

defined('C5_EXECUTE') or die('Access denied');

use Concrete\Core\Form\Service\Form;
use Concrete\Core\Form\Service\Widget\PageSelector;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\View\View;

/** @var string $configuratorId */
/** @var array $configuratorList */
/** @var string $successMessage */
/** @var string $privacyPage */
/** @var string $termsOfUsePage */
/** @var string $thankYouPage */
/** @var bool $displayCaptcha */
/** @var bool $displayTermsOfUse */
/** @var bool $displayPrivacy */


$app = Application::getFacadeApplication();

/** @var PageSelector $pageSelector */
/** @noinspection PhpUnhandledExceptionInspection */
$pageSelector = $app->make(PageSelector::class);
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);

$thankYouPage = $thankYouPage ?? null;

/** @noinspection PhpUnhandledExceptionInspection */
View::element("dashboard/help_blocktypes", [], "simple_configurator");
?>

<div class="form-group">
    <?php echo $form->label("configuratorId", t('Configurator')); ?>
    <?php echo $form->select("configuratorId", $configuratorList, $configuratorId); ?>
</div>

<div class="form-group">
    <?php echo $form->label("successMessage", t('Success Message')); ?>
    <?php echo $form->text("successMessage", $successMessage, ["max-length" => 255]); ?>
</div>

<div class="form-group">
    <?php echo $form->label("thankYouPage", t("Thank You Page")); ?>
    <?php echo $pageSelector->selectPage("thankYouPage", $thankYouPage); ?>
</div>

<div class="form-group">
    <?php echo $form->label("privacyPage", t("Privacy Page")); ?>
    <?php echo $pageSelector->selectPage("privacyPage", $privacyPage); ?>
</div>

<div class="form-group">
    <?php echo $form->label("termsOfUsePage", t("Terms Of Use Page")); ?>
    <?php echo $pageSelector->selectPage("termsOfUsePage", $termsOfUsePage); ?>
</div>

<div class="form-group">
    <div class="form-check">
        <?php echo $form->checkbox('displayTermsOfUse', 1, $displayTermsOfUse, ["class" => "form-check-input"]); ?>
        <?php echo $form->label('displayTermsOfUse', t("Display terms of use checkbox"), ["class" => "form-check-label"]); ?>
    </div>
</div>

<div class="form-group">
    <div class="form-check">
        <?php echo $form->checkbox('displayPrivacy', 1, $displayPrivacy, ["class" => "form-check-input"]); ?>
        <?php echo $form->label('displayPrivacy', t("Display privacy checkbox"), ["class" => "form-check-label"]); ?>
    </div>
</div>

<div class="form-group">
    <div class="form-check">
        <?php echo $form->checkbox('displayCaptcha', 1, $displayCaptcha, ["class" => "form-check-input"]); ?>
        <?php echo $form->label('displayCaptcha', t("Display captcha"), ["class" => "form-check-label"]); ?>
    </div>
</div>
