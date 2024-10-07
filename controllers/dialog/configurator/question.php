<?php /** @noinspection PhpMissingReturnTypeInspection */

/** @noinspection DuplicatedCode */

/** @noinspection PhpUnused */

namespace Concrete\Package\SimpleConfigurator\Controller\Dialog\Configurator;

use Bitter\SimpleConfigurator\Entity\Configurator\QuestionOption;
use Bitter\SimpleConfigurator\Entity\Configurator\Step;
use Concrete\Controller\Backend\UserInterface as BackendInterfaceController;
use Concrete\Core\Application\EditResponse as UserEditResponse;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\File\File;
use Concrete\Core\Form\Service\Validation;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\User\User;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Bitter\SimpleConfigurator\Entity\Configurator\Question as QuestionEntity;
use DateTime;
use Symfony\Component\HttpFoundation\Response;

class Question extends BackendInterfaceController
{
    protected $viewPath = '/dialogs/configurator/question/edit';
    protected EntityManagerInterface $entityManager;
    protected ResponseFactoryInterface $responseFactory;

    public function on_start()
    {
        parent::on_start();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->entityManager = $this->app->make(EntityManagerInterface::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->responseFactory = $this->app->make(ResponseFactoryInterface::class);
    }

    public function add($stepId = null)
    {
        if (isset($stepId) && Uuid::isValid($stepId)) {
            $step = $this->entityManager->getRepository(Step::class)->findOneBy(["id" => Uuid::fromString($stepId)]);

            if ($step instanceof Step) {
                $highestSortIndex = 0;

                try {
                    /** @noinspection PhpDeprecationInspection */
                    /** @noinspection SqlDialectInspection */
                    /** @noinspection SqlNoDataSourceInspection */
                    $highestSortIndex = (int)$this->entityManager->getConnection()->fetchOne("SELECT sortIndex FROM ConfiguratorQuestion WHERE stepId = ? ORDER BY sortIndex DESC LIMIT 1", [
                        $stepId
                    ]);

                } catch (Exception) {
                }

                $sortIndex = $highestSortIndex + 1;
                $uuid = Uuid::uuid4();
                $question = new QuestionEntity($uuid);
                $question->setStep($step);
                $question->setIsEditMode(true);
                $question->setCreatedAt(new DateTime);
                $question->setUpdatedAt(new DateTime);
                $question->setSortIndex($sortIndex);
                $step->getQuestions()->add($question);

                $this->entityManager->persist($step);
                $this->entityManager->persist($question);
                $this->entityManager->flush();

                return $this->responseFactory->redirect(Url::to("/ccm/system/dialogs/simple_configurator/configurator/question/edit/", $uuid), Response::HTTP_TEMPORARY_REDIRECT);
            }
        }
        return $this->responseFactory->notFound(t("Invalid Step."));
    }

    public function remove($questionId = null)
    {
        $r = new UserEditResponse();
        $errorList = new ErrorList();

        if (isset($questionId) && Uuid::isValid($questionId)) {
            $question = $this->entityManager->getRepository(QuestionEntity::class)->findOneBy(["id" => Uuid::fromString($questionId)]);

            if ($question instanceof QuestionEntity) {
                $step = $question->getStep();

                if ($step instanceof Step) {
                    if ($step->getQuestions()->contains($question)) {
                        $step->getQuestions()->removeElement($question);
                    }

                    $this->entityManager->persist($step);
                }

                foreach ($question->getOptions() as $option) {
                    $this->entityManager->remove($option);
                }

                $this->entityManager->remove($question);
                $this->entityManager->flush();

                $r->setMessage(t("The question has been successfully removed."));
                $r->setTitle(t('Question Removed'));
            } else {
                $errorList->add(t("The question does not exists."));
            }
        } else {
            $errorList->add(t("The question does not exists."));
        }

        $r->setError($errorList);
        return $this->responseFactory->json($r);
    }

    public function editPrices($questionId = null)
    {
        if (isset($questionId) && Uuid::isValid($questionId)) {
            $question = $this->entityManager->getRepository(QuestionEntity::class)->findOneBy(["id" => Uuid::fromString($questionId)]);

            if ($question instanceof QuestionEntity) {
                $this->setViewPath("/dialogs/configurator/question/edit_prices");

                /** @var QuestionOption[] $options */
                $options = $this->entityManager->getRepository(QuestionOption::class)->findBy(["question" => $question], ["sortIndex" => "ASC"]);

                $this->set("options", $options);
                $this->set("question", $question);
                /** @noinspection PhpInconsistentReturnPointsInspection */
                return;
            }
        }

        return $this->responseFactory->notFound(t("Invalid Question."));
    }

    public function edit($questionId = null)
    {
        if (isset($questionId) && Uuid::isValid($questionId)) {
            $question = $this->entityManager->getRepository(QuestionEntity::class)->findOneBy(["id" => Uuid::fromString($questionId)]);

            if ($question instanceof QuestionEntity) {
                $questionList = [];

                /** @var QuestionEntity[] $relatedQuestions */
                $relatedQuestions = $this->entityManager->getRepository(QuestionEntity::class)->findAll();

                $questionList[] = t("*** Please select");

                foreach ($relatedQuestions as $relatedQuestionEntry) {
                    if ((string)$relatedQuestionEntry->getId() !== (string)$question->getId()) {
                        $questionList[(string)$relatedQuestionEntry->getId()] = $relatedQuestionEntry->getDisplayName();
                    }
                }

                /** @var QuestionOption[] $options */
                $options = $this->entityManager->getRepository(QuestionOption::class)->findBy(["question" => $question], ["sortIndex" => "ASC"]);

                $this->set("questionList", $questionList);
                $this->set("question", $question);
                $this->set("options", $options);
                /** @noinspection PhpInconsistentReturnPointsInspection */
                return;
            }
        }

        return $this->responseFactory->notFound(t("Invalid Question."));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function submit()
    {
        $r = new UserEditResponse();
        $errorList = new ErrorList();
        /** @var Validation $formValidator */
        $formValidator = $this->app->make(Validation::class);

        $formValidator->setData($this->request->request->all());
        $formValidator->addRequired("name", t("You need to enter a valid name."));
        $formValidator->addRequired("id", t("You need to enter a valid id."));

        if ($formValidator->test()) {
            $questionId = $this->request->request->get("id");

            if (Uuid::isValid($questionId)) {
                $question = $this->entityManager->getRepository(QuestionEntity::class)->findOneBy(["id" => Uuid::fromString($questionId)]);

                if ($question instanceof QuestionEntity) {
                    $question->setName($this->request->request->get("name"));
                    $question->setDescription($this->request->request->get("description"));
                    $question->setTooltip($this->request->request->get("tooltip"));
                    $question->setUpdatedAt(new DateTime());
                    $question->setReference(null);
                    $question->setIsRequired($this->request->request->has("isRequired"));
                    $question->setIsEditMode(false);

                    if (Uuid::isValid($this->request->request->get("reference"))) {
                        $referenceUuid = $this->request->request->get("reference");
                        $reference = $this->entityManager->getRepository(QuestionEntity::class)->findOneBy(["id" => Uuid::fromString($referenceUuid)]);

                        if ($reference instanceof QuestionEntity) {
                            $question->setReference($reference);
                        }
                    }

                    // update options
                    $processedOptionIds = [];

                    if (!$question->getReference() instanceof QuestionEntity) {
                        if ($this->request->request->has("options")) {
                            $options = $this->request->request->get("options", []);

                            if (is_array($options)) {
                                $i = 0;

                                foreach ($options as $optionId => $arrOptionData) {
                                    if (Uuid::isValid($optionId)) {
                                        $i++;

                                        $value = isset($arrOptionData["value"]) ? (string)$arrOptionData["value"] : null;
                                        $fID = isset($arrOptionData["image"]) ? (int)$arrOptionData["image"] : null;
                                        $image = File::getByID($fID);
                                        $optionEntry = $this->entityManager->getRepository(QuestionOption::class)->findOneBy([
                                            "id" => Uuid::fromString($optionId)
                                        ]);

                                        if (!$optionEntry instanceof QuestionOption) {
                                            $optionEntry = new QuestionOption(Uuid::fromString($optionId));
                                            $optionEntry->setQuestion($question);
                                            $optionEntry->setCreatedAt(new DateTime());
                                        }

                                        $optionEntry->setIsEditMode(false);
                                        $optionEntry->setValue($value);
                                        $optionEntry->setImage($image);
                                        $optionEntry->setSortIndex($i);
                                        $optionEntry->setUpdatedAt(new DateTime());
                                        $optionEntry->setReferencedOption(null);

                                        $this->entityManager->persist($optionEntry);

                                        if (!$question->getOptions()->contains($optionEntry)) {
                                            $question->getOptions()->add($optionEntry);
                                        }

                                        $processedOptionIds[] = strtolower($optionId);
                                    }
                                }
                            }
                        }
                    } else {
                        // is reference

                        $i = 0;

                        /** @var QuestionOption[] $options */
                        $referencesOptions = $this->entityManager->getRepository(QuestionOption::class)->findBy(["question" => $question->getReference()], ["sortIndex" => "ASC"]);

                        foreach ($referencesOptions as $referencedOption) {
                            $i++;

                            $optionEntry = $this->entityManager->getRepository(QuestionOption::class)->findOneBy([
                                "referencedOption" => $referencedOption,
                                "question" => $question
                            ]);

                            if (!$optionEntry instanceof QuestionOption) {
                                $optionEntry = new QuestionOption();
                                $optionEntry->setQuestion($question);
                                $optionEntry->setCreatedAt(new DateTime());
                                $optionEntry->setIsEditMode(false);
                            }

                            $optionEntry->setUpdatedAt(new DateTime());
                            $optionEntry->setReferencedOption($referencedOption);
                            $optionEntry->setSortIndex($i);
                            $optionEntry->setValue($referencedOption->getValue());
                            $optionEntry->setImage($referencedOption->getImage());

                            $this->entityManager->persist($optionEntry);

                            if (!$question->getOptions()->contains($optionEntry)) {
                                $question->getOptions()->add($optionEntry);
                            }

                            $processedOptionIds[] = strtolower($optionEntry->getId());
                        }
                    }

                    // remove unused options
                    foreach ($question->getOptions() as $option) {
                        if (!in_array(strtolower($option->getId()), $processedOptionIds)) {
                            $this->entityManager->remove($option);
                        }
                    }

                    $this->entityManager->persist($question);
                    $this->entityManager->flush();

                    $r->setMessage(t("The question has been updated successfully."));
                    $r->setTitle(t('Question Updated'));
                } else {
                    $errorList->add(t("The given id can not be found."));
                }
            } else {
                $errorList->add(t("The given id is not a valid uuid."));
            }
        } else {
            $errorList = $formValidator->getError();
        }

        $r->setError($errorList);

        return $this->responseFactory->json($r);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function submitPrices()
    {
        $r = new UserEditResponse();
        $errorList = new ErrorList();

        $questionId = $this->request->request->get("id");

        if (Uuid::isValid($questionId)) {
            $question = $this->entityManager->getRepository(QuestionEntity::class)->findOneBy(["id" => Uuid::fromString($questionId)]);

            if ($question instanceof QuestionEntity) {
                foreach($this->request->request->get("options") as $optionId => $arrData) {
                    $price =(float)$arrData["price"];

                    foreach($question->getOptions() as $option) {
                        if ($option->getId() == $optionId) {
                            $option->setPrice($price);
                            $this->entityManager->persist($option);
                        }
                    }
                }

                $this->entityManager->persist($question);
                $this->entityManager->flush();

                $r->setMessage(t("The prices has been updated successfully."));
                $r->setTitle(t('Prices Updated'));
            } else {
                $errorList->add(t("The given id can not be found."));
            }
        } else {
            $errorList->add(t("The given id is not a valid uuid."));
        }

        $r->setError($errorList);

        return $this->responseFactory->json($r);
    }

    protected function canAccess(): bool
    {
        $user = new User();

        foreach($user->getUserGroups() as $g) {
            if ($g === ADMIN_GROUP_ID) {
                return true;
            }
        }

        return $user->isSuperUser();
    }
}

