<?php /** @noinspection DuplicatedCode */

defined('C5_EXECUTE') or die('Access denied');

use Bitter\SimpleConfigurator\Attribute\Key\SubmissionKey;
use Bitter\SimpleConfigurator\Entity\Configurator\Step;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\View\View;
use Concrete\Package\SimpleConfigurator\Block\Configurator\Controller;
use Concrete\Core\Attribute\Form\Renderer;
use Concrete\Core\Captcha\CaptchaInterface;
use Concrete\Core\Form\Service\Form;
use HtmlObject\Element;

/** @var array $activeStepPositionValues */
/** @var bool $displayCaptcha */
/** @var bool $displayTermsOfUse */
/** @var bool $displayPrivacy */
/** @var int $privacyPage */
/** @var int $termsOfUsePage */
/** @var string $stepTitle */
/** @var string $activeStepId */
/** @var string|null $prevStepId */
/** @var string|null $nextStepId */
/** @var Renderer $renderer */
/** @var SubmissionKey[] $attributes */
/** @var Controller $controller */
/** @var bool $isFirstStep */
/** @var bool $isLastStep */
/** @var Step $activeStep */
/** @var Step|null $prevStep */
/** @var Step|null $nextStep */
/** @var array $steps */
/** @var string|null $success */
/** @var ErrorList $errorList */

$app = Application::getFacadeApplication();
/** @var Token $token */
/** @noinspection PhpUnhandledExceptionInspection */
$token = $app->make(Token::class);
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var CaptchaInterface $captcha */
/** @noinspection PhpUnhandledExceptionInspection */
$captcha = $app->make(CaptchaInterface::class);
?>

<div class="configurator">
    <?php
    /** @noinspection PhpUnhandledExceptionInspection */
    View::element(
        'system_errors',
        array(
            'format' => 'block',
            'error' => $errorList,
            'success' => $success,
            'message' => null
        )
    );
    ?>

    <form action="<?php echo $controller->getActionURL("step", $activeStepId); ?>" method="post">
        <?php $token->output("update_step"); ?>

        <div class="row">
            <div class="col-sm-12">

                <div class="progressbar">
                    <div class="hr"></div>

                    <?php foreach ($steps as $stepId => $stepName) { ?>
                        <div class="<?php echo $stepId === $activeStepId ? "active" : "" ?> btn-step">
                            <?php echo $stepName; ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <section class="content">
                    <?php if ($isLastStep) { ?>
                        <?php if (!empty($attributes)) { ?>
                            <?php foreach ($attributes as $ak) { ?>
                                <?php $renderer->buildView($ak)->render(); ?>
                            <?php } ?>
                        <?php } ?>

                        <?php if ($displayPrivacy): ?>
                            <div class="privacy-checkbox">
                                <div class="form-check">
                                    <?php echo $form->checkbox("acceptPrivacy", 1, false, ["class" => "form-check-input"]); ?>

                                    <label for="acceptPrivacy" class="form-check-label">
                                        <?php
                                        $privacyPageLinkText = t("privacy policy");

                                        $page = Page::getByID($privacyPage);

                                        if (!$page->isError()) {
                                            $linkTag = new Element("a");
                                            $linkTag->setAttribute("href", (string)Url::to($page));
                                            $linkTag->setAttribute("target", "_blank");
                                            $linkTag->setValue($privacyPageLinkText);
                                            $privacyPageLinkText = (string)$linkTag;
                                        }

                                        echo t("I hereby acknowledge that the data collected in this form will be stored for further use and deleted once my inquiry has been processed. Note: You can revoke your consent at any time by drop us an email. See more detailed information on how we use user-data in our %s.", $privacyPageLinkText);
                                        ?>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($displayTermsOfUse): ?>
                            <div class="terms-of-use-checkbox">
                                <div class="form-check">
                                    <?php echo $form->checkbox("acceptTermsOfUse", 1, false, ["class" => "form-check-input"]); ?>

                                    <label for="acceptTermsOfUse" class="form-check-label">
                                        <?php
                                        $termsOfUsePageLinkText = t("terms of use");

                                        $page = Page::getByID($termsOfUsePage);

                                        if (!$page->isError()) {
                                            $linkTag = new Element("a");
                                            $linkTag->setAttribute("href", (string)Url::to($page));
                                            $linkTag->setAttribute("target", "_blank");
                                            $linkTag->setValue($termsOfUsePageLinkText);
                                            $termsOfUsePageLinkText = (string)$linkTag;
                                        }

                                        echo t("I accept the %s.", $termsOfUsePageLinkText);
                                        ?>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($displayCaptcha): ?>
                            <div class="captcha">
                                <?php echo $captcha->display(); ?>

                                <div class="captcha-input">
                                    <?php echo $captcha->showInput(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php } else { ?>
                        <?php echo $activeStep->render($activeStepPositionValues); ?>
                    <?php } ?>
                </section>

                <div class="float-end">
                    <?php if ($isFirstStep) { ?>
                        <a href="<?php echo Url::to(Page::getCurrentPage(), "step", $activeStepId) ?>"
                           class="btn btn-secondary disabled">
                            <?php echo t("Back"); ?>
                        </a>
                    <?php } else { ?>
                        <a href="<?php echo Url::to(Page::getCurrentPage(), "step", $prevStepId) ?>"
                           class="btn btn-secondary">
                            <?php echo t("Back"); ?>
                        </a>
                    <?php } ?>

                    <button type="submit" class="btn btn-secondary">
                        <?php echo $isLastStep ? t("Submit") : t("Next"); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>