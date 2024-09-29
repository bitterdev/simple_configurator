<?php /** @noinspection DuplicatedCode */

/** @noinspection PhpUnused */

namespace Bitter\SimpleConfigurator\Backup\ContentImporter\Importer\Routine;

use Bitter\SimpleConfigurator\Entity\Configurator;
use Bitter\SimpleConfigurator\Entity\Configurator\Question;
use Bitter\SimpleConfigurator\Entity\Configurator\QuestionOption;
use Bitter\SimpleConfigurator\Entity\Configurator\Step;
use Concrete\Core\Backup\ContentImporter\Importer\Routine\AbstractRoutine;
use Concrete\Core\Backup\ContentImporter\ValueInspector\ValueInspector;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\File\File;
use Concrete\Core\Site\Service;
use Concrete\Core\Support\Facade\Application;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use SimpleXMLElement;
use DateTime;

class ImportConfiguratorRoutine extends AbstractRoutine
{
    public function getHandle(): string
    {
        return 'configurator';
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function import(SimpleXMLElement $element)
    {
        $app = Application::getFacadeApplication();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $app->make(EntityManagerInterface::class);
        /** @var Service $siteService */
        $siteService = $app->make(Service::class);
        $defaultSite = $siteService->getSite();
        /** @var ValueInspector $valueInspector */
        $valueInspector = $app->make('import/value_inspector');
        /** @var Site $site */
        $site = $app->make('site')->getActiveSiteForEditing();
        $defaultLocale = "";

        foreach ($site->getLocales() as $localeEntity) {
            if ($localeEntity->getIsDefault()) {
                $defaultLocale = $localeEntity->getLocale();
            }
        }

        if (isset($element->configurators)) {
            foreach ($element->configurators->configurator as $configurator) {
                $locale = (string)$configurator["locale"];

                $site = $defaultSite;

                if (isset($configurator["site"])) {
                    $siteObject = $siteService->getByHandle($configurator["site"]);

                    if ($siteObject instanceof Site) {
                        $site = $siteObject;
                    }
                }

                if (strlen($locale) === 0) {
                    $locale = $defaultLocale;
                }

                $configuratorEntry = null;

                if (isset($configurator["id"]) && Uuid::isValid((string)$configurator["id"])) {
                    $configuratorEntry = $entityManager->getRepository(Configurator::class)->findOneBy(["id" => Uuid::fromString((string)$configurator["id"])]);
                }

                if (!$configuratorEntry instanceof Configurator) {
                    $configuratorEntry = new Configurator();
                    $configuratorEntry->setId(Uuid::fromString((string)$configurator["id"]));
                    $configuratorEntry->setCreatedAt( new DateTime());
                }

                $configuratorEntry->setUpdatedAt(new DateTime());
                $configuratorEntry->setLocale($locale);
                $configuratorEntry->setSite($site);
                $configuratorEntry->setName((string)$configurator["name"]);
                $configuratorEntry->setIsEditMode(false);
                $configuratorEntry->setPackage(static::getPackageObject($configurator['package']));

                $entityManager->persist($configuratorEntry);
                $entityManager->flush();

                $a = 0;


                foreach ($configurator->steps->step as $step) {
                    $a++;

                    $locale = (string)$step["locale"];

                    $site = $defaultSite;

                    if (isset($step["site"])) {
                        $siteObject = $siteService->getByHandle($step["site"]);

                        if ($siteObject instanceof Site) {
                            $site = $siteObject;
                        }
                    }

                    if (strlen($locale) === 0) {
                        $locale = $defaultLocale;
                    }

                    $stepEntry = null;

                    if (isset($step["id"]) && Uuid::isValid((string)$step["id"])) {
                        $stepEntry = $entityManager->getRepository(Step::class)->findOneBy(["id" => Uuid::fromString((string)$step["id"])]);
                    }

                    if (!$stepEntry instanceof Step) {
                        if (isset($step["id"]) && Uuid::isValid((string)$step["id"])) {
                            $stepEntry = new Step(Uuid::fromString((string)$step["id"]));
                        } else {
                            $stepEntry = new Step();
                        }

                        $stepEntry->setCreatedAt(new DateTime());
                    }

                    $stepEntry->setConfigurator($configuratorEntry);
                    $stepEntry->setUpdatedAt(new DateTime());
                    $stepEntry->setLocale($locale);
                    $stepEntry->setSite($site);
                    $stepEntry->setName((string)$step["name"]);
                    $stepEntry->setIsEditMode(false);
                    $stepEntry->setSortIndex(isset($question["sort-index"]) && is_numeric($step["sort-index"]) ? (int)$step["sort-index"] : $a);
                    $stepEntry->setPackage(static::getPackageObject($step['package']));

                    $entityManager->persist($stepEntry);
                    $entityManager->flush();

                    if (isset($step->questions)) {
                        $b = 0;

                        foreach ($step->questions->question as $question) {
                            $b++;

                            $questionEntry = null;

                            if (isset($question["id"]) && Uuid::isValid($question["id"])) {
                                $questionEntry = $entityManager->getRepository(Question::class)->findOneBy(["id" => Uuid::fromString((string)$question["id"])]);
                            }

                            if (!$questionEntry instanceof Question) {
                                if (isset($question["id"]) && Uuid::isValid($question["id"])) {
                                    $questionEntry = new Question(Uuid::fromString((string)$question["id"]));
                                } else {
                                    $questionEntry = new Question();
                                }

                                $questionEntry->setCreatedAt(new DateTime());
                            }

                            $questionEntry->setUpdatedAt(new DateTime());
                            $questionEntry->setName((string)$question["name"]);
                            $questionEntry->setIsEditMode(false);
                            $questionEntry->setDescription((string)$question["description"]);
                            $questionEntry->setTooltip((string)$question["tooltip"]);
                            $questionEntry->setIsRequired(isset($question["is-required"]) && (int)$question["is-required"] === 1);
                            $questionEntry->setPackage(static::getPackageObject($question['package']));
                            $questionEntry->setSortIndex(isset($question["sort-index"]) && is_numeric($question["sort-index"]) ? (int)$question["sort-index"] : $b);
                            $questionEntry->setStep($stepEntry);

                            if (!$stepEntry->getQuestions()->contains($questionEntry)) {
                                $stepEntry->getQuestions()->add($questionEntry);
                            }

                            if (isset($question["reference"]) && Uuid::isValid($question["reference"])) {
                                $referenceEntry = $entityManager->getRepository(Question::class)->findOneBy(["id" => Uuid::fromString((string)$question["reference"])]);

                                if ($referenceEntry instanceof Question) {
                                    $questionEntry->setReference($referenceEntry);
                                }
                            }

                            $entityManager->persist($stepEntry);
                            $entityManager->persist($questionEntry);
                            $entityManager->flush();

                            if (isset($question->options)) {
                                $c = 0;

                                foreach ($question->options->option as $option) {
                                    $c++;

                                    $optionEntry = null;

                                    if (isset($option["id"]) && Uuid::isValid($option["id"])) {
                                        $optionEntry = $entityManager->getRepository(QuestionOption::class)->findOneBy(["id" => Uuid::fromString((string)$option["id"])]);
                                    }

                                    if (!$optionEntry instanceof QuestionOption) {
                                        if (isset($question["id"]) && Uuid::isValid($option["id"])) {
                                            $optionEntry = new QuestionOption(Uuid::fromString((string)$option["id"]));
                                        } else {
                                            $optionEntry = new QuestionOption();
                                        }

                                        $optionEntry->setCreatedAt(new DateTime());
                                    }

                                    $optionEntry->setUpdatedAt(new DateTime());
                                    $optionEntry->setValue((string)$option["value"]);
                                    $optionEntry->setPrice((float)$option["price"]);
                                    $optionEntry->setIsEditMode(false);
                                    $optionEntry->setSortIndex(isset($option["sort-index"]) && is_numeric($option["sort-index"]) ? (int)$option["sort-index"] : $c);
                                    $optionEntry->setImage(File::getByID($valueInspector->inspect((string)$option["image"])->getReplacedValue()));
                                    $optionEntry->setPackage(static::getPackageObject($option['package']));
                                    $optionEntry->setQuestion($questionEntry);
                                    $optionEntry->setReferencedOption(null);

                                    if ($questionEntry->getReference() instanceof Question) {
                                        foreach($questionEntry->getReference()->getOptions() as $referencedOptionEntry) {
                                            if ($referencedOptionEntry->getValue() === $optionEntry->getValue()) {
                                                $optionEntry->setReferencedOption($referencedOptionEntry);
                                            }
                                        }
                                    }

                                    if (!$questionEntry->getOptions()->contains($optionEntry)) {
                                        $questionEntry->getOptions()->add($optionEntry);
                                    }

                                    $entityManager->persist($optionEntry);
                                    $entityManager->persist($questionEntry);
                                    $entityManager->flush();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}