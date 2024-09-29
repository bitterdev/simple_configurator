<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Bitter\SimpleConfigurator\Entity\Configurator\Question;
use Bitter\SimpleConfigurator\Entity\Configurator\Step;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\SimpleConfigurator\Controller;
use Doctrine\ORM\EntityManagerInterface;

/** @var Step $step */
/** @var array $storedPositionValues */

$app = Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var PackageService $packageService */
/** @noinspection PhpUnhandledExceptionInspection */
$packageService = $app->make(PackageService::class);
/** @var EntityManagerInterface $entityManager */
/** @noinspection PhpUnhandledExceptionInspection */
$entityManager = $app->make(EntityManagerInterface::class);
$pkgEntity = $packageService->getByHandle("simple_configurator");
/** @var Controller $pkg */
$pkg = $pkgEntity->getController();

$defaultImageUrl = $pkg->getRelativePath() . "/images/configurator_default_image.jpg";

$questions = $entityManager->getRepository(Question::class)->findBy([
    "step" => $step
], ["sortIndex" => "ASC"]);
?>

<?php foreach ($questions as $question) { ?>
    <div class="configurator-question">

            <p>
                <strong>
                    <?php echo $question->getName(); ?>

                    <?php if ($question->hasTooltip()) { ?>
                        <span class="question-tooltip" data-bs-toggle="tooltip" title="<?php echo h($question->getTooltip()) ?>">
                            <i class="fa fa-info-circle px-1" aria-hidden="true"></i>
                        </span>
                    <?php } ?>
                </strong>
            </p>

            <p class="description">
                <?php echo $question->getDescription(); ?>
            </p>

        <div class="configurator-options">
            <?php if ($question->hasImageOptions()) { ?>
                <div class="image-wrapper">
                    <?php foreach ($question->getOptions() as $option) { ?>
                        <?php
                        $imageUrl = $defaultImageUrl;

                        $f = $option->getImage();

                        if ($f instanceof File) {
                            $fv = $f->getApprovedVersion();

                            if ($fv instanceof Version) {
                                $imageUrl = $fv->getThumbnailURL("configurator_image");
                            }
                        }
                        ?>

                        <div class="configurator-option image">
                            <label for="option_<?php echo $question->getId() ?>">
                                <!--suppress HtmlFormInputWithoutLabel -->
                                <input type="radio" name="question[<?php echo $question->getId() ?>]"
                                       value="<?php echo h($option->getId()); ?>"
                                       id="option_<?php echo $option->getId() ?>"
                                    <?php echo isset($storedPositionValues[(string)$question->getId()]) && $storedPositionValues[(string)$question->getId()] === $option->getId() ? " checked" : "" ?> />
                                <img src="<?php echo h($imageUrl); ?>" alt="<?php echo h($option->getValue()) ?>"/>
                                <span class="value"><?php echo $option->getValue(); ?></span>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="configurator-option">
                    <?php echo $form->select("question[" . (string)$question->getId() . "]", $question->getOptionList(), $storedPositionValues[(string)$question->getId()] ?? null, ["id" => "question_" . (string)$question->getId()]); ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
