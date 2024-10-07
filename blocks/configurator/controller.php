<?php /** @noinspection DuplicatedCode */

/** @noinspection PhpInconsistentReturnPointsInspection */

namespace Concrete\Package\SimpleConfigurator\Block\Configurator;

use Bitter\SimpleConfigurator\Attribute\Category\ConfiguratorCategory;
use Bitter\SimpleConfigurator\Entity\Attribute\Key\SubmissionKey;
use Bitter\SimpleConfigurator\Entity\Attribute\Value\SubmissionValue;
use Bitter\SimpleConfigurator\Entity\Configurator;
use Bitter\SimpleConfigurator\Entity\Configurator\Question;
use Bitter\SimpleConfigurator\Entity\Configurator\QuestionOption;
use Bitter\SimpleConfigurator\Entity\Configurator\Step;
use Bitter\SimpleConfigurator\Entity\Configurator\Submission;
use Bitter\SimpleConfigurator\Entity\Configurator\SubmissionPosition;
use Bitter\SimpleConfigurator\Submission\SubmissionInfo;
use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Attribute\Context\FrontendFormContext;
use Concrete\Core\Attribute\Form\Renderer;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Captcha\CaptchaInterface;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Form\Service\Validation;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Page;
use Concrete\Core\Site\Service;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Mail\Service as MailService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use DateTime;

class Controller extends BlockController
{
    protected $btTable = 'btConfigurator';
    protected $btInterfaceWidth = 400;
    protected $btInterfaceHeight = 500;
    protected $btCacheBlockOutputLifetime = 300;
    protected ?EntityManagerInterface $entityManager;
    protected ?ResponseFactoryInterface $responseFactory;
    protected ?Service $siteService;
    protected ?Site $site;
    protected ?SessionInterface $session;
    protected ?string $locale;
    protected ?array $cachedSteps = null;
    protected ErrorList $errorList;
    protected ?string $success = null;
    /** @var CaptchaInterface */
    protected CaptchaInterface $captcha;
    /** @var MailService */
    protected MailService $mailService;

    const LAST_STEP_ID = "c381bc12-3a20-11ef-b223-325096b39f47";

    public function getBlockTypeDescription(): string
    {
        return t('Add a configurator to your site.');
    }

    public function getBlockTypeName(): string
    {
        return t("Configurator");
    }

    public function registerViewAssets($outputContent = '')
    {
        parent::registerViewAssets($outputContent);

        $this->requireAsset("css", "font-awesome");
        $this->requireAsset("core/cms");
    }

    public function save($args)
    {
        $args["displayCaptcha"] = isset($args["displayCaptcha"]) ? 1 : 0;
        $args["displayTermsOfUse"] = isset($args["displayTermsOfUse"]) ? 1 : 0;
        $args["displayPrivacy"] = isset($args["displayPrivacy"]) ? 1 : 0;

        parent::save($args);
    }

    protected function setEditDefaults()
    {
        $configuratorList = [];
        /** @var EntityManagerInterface $entityManager */
        /** @noinspection PhpUnhandledExceptionInspection */
        $entityManager = $this->app->make(EntityManagerInterface::class);
        $configurators = $entityManager->getRepository(Configurator::class)->findBy([], []);
        foreach ($configurators as $configurator) {
            /** @var $configurator Configurator */
            $configuratorList[(string)$configurator->getId()] = $configurator->getDisplayName();
        }
        $this->set("configuratorList", $configuratorList);
    }

    public function add()
    {
        $this->set("privacyPage", null);
        $this->set("termsOfUsePage", null);
        $this->set("thankYouPage", null);
        $this->set("displayCaptcha", true);
        $this->set("configuratorId", true);
        $this->set("displayTermsOfUse", true);
        $this->set("successMessage", t("Thank you for your request. We will reply as soon as possible."));
        $this->set("displayPrivacy", true);

        $this->setEditDefaults();
    }

    public function edit()
    {
        $this->setEditDefaults();
    }


    /** @noinspection PhpUnhandledExceptionInspection */
    public function on_start()
    {
        parent::on_start();

        $this->entityManager = $this->app->make(EntityManagerInterface::class);
        $this->responseFactory = $this->app->make(ResponseFactoryInterface::class);
        $this->siteService = $this->app->make(Service::class);
        $this->session = $this->app->make('session');
        $this->site = $this->siteService->getSite();
        $this->errorList = new ErrorList();
        $this->captcha = $this->app->make(CaptchaInterface::class);
        $this->mailService = $this->app->make(MailService::class);

        foreach ($this->site->getLocales() as $localeEntity) {
            if ($localeEntity->getIsDefault()) {
                $this->locale = $localeEntity->getLocale();
            }
        }
    }

    protected function getFirstStepId(): string
    {
        $steps = array_keys(array_reverse($this->getSteps()));
        return array_pop($steps);
    }

    protected function getLastStepId(): string
    {
        return self::LAST_STEP_ID;
    }

    protected function getActiveStepId(): string
    {
        return (string)$this->session->get("simple_configurator.configurator.active_step_id", $this->getFirstStepId());
    }

    protected function getActiveStep(): ?Step
    {
        return $this->getStepEntryById($this->getActiveStepId());
    }

    protected function isValidStepId(?string $stepId = null): bool
    {
        return isset($stepId) && Uuid::isValid($stepId) && in_array(strtolower($stepId), array_keys($this->getSteps()));
    }

    protected function setActiveStepId(string $stepId = null): bool
    {
        if ($this->isValidStepId($stepId)) {
            $this->session->set("simple_configurator.configurator.active_step_id", $stepId);
            $this->session->save();

            return true;
        } else {
            return false;
        }
    }

    protected function getNextStepId(): ?string
    {
        $nextStepId = null;
        $stepId = $this->getActiveStepId();
        $steps = $this->getSteps();

        if (!$this->isLastStep()) {
            $stepIds = array_keys($steps);
            $ordinal = (array_search($stepId, $stepIds) + 1) % count($stepIds);
            $nextStepId = $stepIds[$ordinal];
        }


        return $nextStepId;
    }

    protected function isLastStep(): bool
    {
        return $this->getActiveStepId() === $this->getLastStepId();
    }

    protected function isFirstStep(): bool
    {
        return $this->getActiveStepId() === $this->getFirstStepId();
    }

    protected function getPrevStepId(): ?string
    {
        $prevStepId = null;
        $stepId = $this->getActiveStepId();
        $steps = $this->getSteps();

        if (!$this->isFirstStep()) {
            $stepIds = array_keys($steps);
            $ordinal = (array_search($stepId, $stepIds) - 1) % count($stepIds);
            $prevStepId = $stepIds[$ordinal];
        }

        return $prevStepId;
    }

    protected function getNextStep(): ?Step
    {
        return $this->getStepEntryById($this->getNextStepId());
    }

    protected function getPrevStep(): ?Step
    {
        return $this->getStepEntryById($this->getPrevStepId());
    }

    protected function getStepEntryById(?string $stepId): ?Step
    {
        if ($this->isValidStepId($stepId)) {
            return $this->entityManager->getRepository(Step::class)->findOneBy([
                "site" => $this->site,
                "locale" => $this->locale,
                "id" => Uuid::fromString($stepId)
            ]);
        }

        return null;
    }

    protected function getStepTitle(): ?string
    {
        $steps = $this->getSteps();
        return $steps[$this->getActiveStepId()];
    }

    protected function saveActiveStepPositionValues(array $submittedPositionValues = [])
    {
        $storedPositionValues = $this->getActiveStepPositionValues();

        foreach ($submittedPositionValues as $submittedQuestionId => $submittedValue) {
            $storedPositionValues[(string)$submittedQuestionId] = $submittedValue;
        }

        $this->session->set("simple_configurator.configurator.steps.submitted_questions", $storedPositionValues);
        $this->session->save();
    }

    protected function getActiveStepPositionValues(): array
    {
        return $this->session->get("simple_configurator.configurator.steps.submitted_questions", []);
    }

    protected function setDefaults()
    {
        $this->set("activeStepId", $this->getActiveStepId());
        $this->set("activeStepPositionValues", $this->getActiveStepPositionValues());
        $this->set("prevStepId", $this->getPrevStepId());
        $this->set("nextStepId", $this->getNextStepId());
        $this->set("activeStep", $this->getActiveStep());
        $this->set("prevStep", $this->getPrevStep());
        $this->set("nextStep", $this->getNextStep());
        $this->set("steps", $this->getSteps());
        $this->set("isFirstStep", $this->isFirstStep());
        $this->set("isLastStep", $this->isLastStep());
        $this->set("stepTitle", $this->getStepTitle());
        $this->set("success", $this->success);
        $this->set("errorList", $this->errorList);
        $this->set('renderer', new Renderer(new FrontendFormContext()));
        /** @noinspection PhpUnhandledExceptionInspection */
        $service = $this->app->make(CategoryService::class);
        $categoryEntity = $service->getByHandle('configurator');
        /** @var ConfiguratorCategory $category */
        $category = $categoryEntity->getController();
        $setManager = $category->getSetManager();
        /** @var SubmissionKey[] $attributes */
        $attributes = [];

        foreach ($setManager->getUnassignedAttributeKeys() as $ak) {
            $attributes[] = $ak;
        }

        $this->set('attributes', $attributes);
    }

    public function action_step($stepId = null)
    {
        if ($this->isValidStepId($stepId)) {
            if ($this->request->getMethod() === "POST") {
                /** @var Validation $formValidator */
                /** @noinspection PhpUnhandledExceptionInspection */
                $formValidator = $this->app->make(Validation::class);

                $formValidator->setData($this->request->request->all());
                $formValidator->addRequiredToken("update_step");

                if ($formValidator->test()) {
                    if ($this->isLastStep()) {
                        /** @noinspection PhpUndefinedFieldInspection */
                        if ($this->displayPrivacy) {
                            $formValidator->addRequired("acceptPrivacy", t("You need to accept the privacy."));
                        }

                        /** @noinspection PhpUndefinedFieldInspection */
                        if ($this->displayTermsOfUse) {
                            $formValidator->addRequired("acceptTermsOfUse", t("You need to accept the terms of use."));
                        }

                        /** @noinspection PhpUndefinedFieldInspection */
                        if ($this->displayCaptcha && !$this->captcha->check()) {
                            $this->errorList->add(t("The given captcha is invalid."));
                        }

                        if (!$formValidator->test()) {
                            $this->errorList = $formValidator->getError();
                        }

                        if (!$this->errorList->has()) {
                            /** @var CategoryService $service */
                            /** @noinspection PhpUnhandledExceptionInspection */
                            $service = $this->app->make(CategoryService::class);
                            $categoryEntity = $service->getByHandle('configurator');
                            /** @var ConfiguratorCategory $category */
                            $category = $categoryEntity->getController();
                            $setManager = $category->getSetManager();
                            /** @var SubmissionValue[] $attributes */
                            $attributes = [];

                            foreach ($setManager->getUnassignedAttributeKeys() as $ak) {
                                $attributes[] = $ak;
                            }

                            /** @var Service $siteService */
                            /** @noinspection PhpUnhandledExceptionInspection */
                            $siteService = $this->app->make(Service::class);
                            $site = $siteService->getSite();
                            $config = $site->getConfigRepository();

                            foreach ($attributes as $uak) {
                                /** @var SubmissionKey $uak */
                                $controller = $uak->getController();

                                $validator = $controller->getValidator();
                                $response = $validator->validateSaveValueRequest(
                                    $controller,
                                    $this->request
                                );

                                if (!$response->isValid()) {
                                    $error = $response->getErrorObject();
                                    $this->errorList->add($error);
                                }
                            }

                            if (!$this->errorList->has()) {
                                $submission = new Submission();

                                $submission->setUpdatedAt(new DateTime());
                                $submission->setCreatedAt(new DateTime());

                                $this->entityManager->persist($submission);
                                $this->entityManager->flush();

                                /** @var SubmissionInfo $submissionInfo */
                                /** @noinspection PhpUnhandledExceptionInspection */
                                $submissionInfo = $this->app->make(SubmissionInfo::class);
                                $submissionInfo->setEntity($submission);
                                /** @noinspection PhpParamsInspection */
                                $submissionInfo->saveUserAttributesForm($attributes);

                                foreach ($this->getActiveStepPositionValues() as $questionId => $storedValue) {
                                    if (Uuid::isValid($questionId)) {
                                        $question = $this->entityManager->getRepository(Question::class)->findOneBy([
                                            "id" => Uuid::fromString($questionId)
                                        ]);

                                        if ($question instanceof Question) {
                                            if ($question->getReference() instanceof Question) {
                                                if ($storedValue === "yes" && isset($this->getActiveStepPositionValues()[(string)$question->getReference()->getId()])) {
                                                    $referencedOptionId = $this->getActiveStepPositionValues()[(string)$question->getReference()->getId()];

                                                    foreach ($question->getOptions() as $option) {
                                                        if ($option->getReferencedOption() instanceof QuestionOption &&
                                                            (string)$option->getReferencedOption()->getId() === $referencedOptionId) {

                                                            $position = new SubmissionPosition();
                                                            $position->setCreatedAt(new DateTime());
                                                            $position->setUpdatedAt(new DateTime());
                                                            $position->setSubmission($submission);
                                                            $position->setLabel($question->getDisplayName());
                                                            $position->setValue(t("Yes"));
                                                            $position->setPrice($option->getPrice());
                                                            $this->entityManager->persist($position);
                                                            $submission->getPositions()->add($position);
                                                        }
                                                    }
                                                }
                                            } else {
                                                foreach ($question->getOptions() as $option) {
                                                    if ((string)$option->getId() === $storedValue) {
                                                        $position = new SubmissionPosition();
                                                        $position->setCreatedAt(new DateTime());
                                                        $position->setUpdatedAt(new DateTime());
                                                        $position->setSubmission($submission);
                                                        $position->setLabel($question->getDisplayName());
                                                        $position->setValue($option->getValue());
                                                        $position->setPrice($option->getPrice());
                                                        $this->entityManager->persist($position);
                                                        $submission->getPositions()->add($position);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                $this->entityManager->flush();

                                $notificationEmail = $config->get("simple_configurator.configurator.notification_mail_address");

                                if (filter_var($notificationEmail, FILTER_VALIDATE_EMAIL)) {
                                    $this->mailService->addParameter("submission", $submission);

                                    $this->mailService->to($notificationEmail);
                                    $this->mailService->load("new_configurator_submission", "simple_configurator");
                                    /** @noinspection PhpUnhandledExceptionInspection */
                                    $this->mailService->sendMail();
                                }

                                //$this->session->clear();

                                $this->success = $this->get("successMessage");

                                /** @noinspection PhpUndefinedFieldInspection */
                                if ((int)$this->thankYouPage > 0) {
                                    /** @noinspection PhpUndefinedFieldInspection */
                                    $thankYouPage = Page::getByID($this->thankYouPage);

                                    if (!$thankYouPage->isError()) {
                                        return $this->responseFactory->redirect(Url::to($thankYouPage));
                                    }
                                }
                            }
                        }
                    } else {
                        $this->errorList = $this->getActiveStep()->validate($this->request->request->all());

                        if (!$this->errorList->has()) {
                            $this->saveActiveStepPositionValues($this->getActiveStep()->transformToPositionValues($this->request->request->all()));

                            return $this->responseFactory->redirect(Url::to(Page::getCurrentPage(), "step", $this->getNextStepId()));
                        }
                    }
                } else {
                    foreach ($formValidator->getError() as $error) {
                        $this->errorList->add($error);
                    }
                }
            }

            $this->setActiveStepId($stepId);
            $this->setDefaults();
            return;
        }

        return $this->responseFactory->notFound(t("Not Found"));
    }

    public function view()
    {
        // render first step
        $this->action_step($this->getFirstStepId());
    }

    protected function getSteps(): array
    {
        if (is_null($this->cachedSteps)) {
            $steps = [];

            /** @noinspection PhpUndefinedFieldInspection */
            $configurator = $this->entityManager->getRepository(Configurator::class)->findOneBy(["id" => $this->configuratorId]);

            if ($configurator instanceof Configurator) {
                $stepEntries = $this->entityManager->getRepository(Step::class)->findBy([
                    "site" => $this->site,
                    "configurator" => $configurator,
                    "locale" => $this->locale
                ], ["sortIndex" => "ASC"]);

                foreach ($stepEntries as $stepEntry) {
                    if ($stepEntry instanceof Step) {
                        $steps[strtolower($stepEntry->getId())] = $stepEntry->getDisplayName();
                    }
                }

                $steps[strtolower($this->getLastStepId())] = t("Personal Data");
            }

            $this->cachedSteps = $steps;
        }

        return $this->cachedSteps;
    }
}