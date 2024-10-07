<?php /** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpMissingReturnTypeInspection */

/** @noinspection PhpUnused */

namespace Concrete\Package\SimpleConfigurator\Controller\SinglePage\Dashboard\SimpleConfigurator;

use Bitter\SimpleConfigurator\Entity\Configurator;
use Bitter\SimpleConfigurator\Entity\Configurator\Question;
use Bitter\SimpleConfigurator\Entity\Configurator\Step;
use Concrete\Core\Application\EditResponse as UserEditResponse;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Form\Service\Validation;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Url;
use Doctrine\DBAL\Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class Configurators extends DashboardPageController
{
    protected ResponseFactoryInterface $responseFactory;
    protected Validation $formValidator;

    public function on_start()
    {
        parent::on_start();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->responseFactory = $this->app->make(ResponseFactoryInterface::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->formValidator = $this->app->make(Validation::class);
    }

    public function add_step($configuratorId = null): Response
    {
        if (isset($configuratorId) && Uuid::isValid($configuratorId)) {
            $configurator = $this->getEntityManager()->getRepository(Configurator::class)->findOneBy(["id" => $configuratorId]);

            if ($configurator instanceof Configurator) {
                /** @var Site $site */
                /** @noinspection PhpUnhandledExceptionInspection */
                $site = $this->app->make('site')->getActiveSiteForEditing();
                $defaultLocale = "";

                foreach ($site->getLocales() as $localeEntity) {
                    if ($localeEntity->getIsDefault()) {
                        $defaultLocale = $localeEntity->getLocale();
                    }
                }

                $highestSortIndex = 0;

                try {
                    /** @noinspection PhpDeprecationInspection */
                    /** @noinspection SqlDialectInspection */
                    /** @noinspection SqlNoDataSourceInspection */
                    $highestSortIndex = (int)$this->entityManager->getConnection()->fetchOne("SELECT sortIndex FROM ZweifelConfiguratorStep ORDER BY sortIndex DESC LIMIT 1");
                } catch (Exception) {
                }

                $sortIndex = $highestSortIndex + 1;

                $step = new Step();
                $uuid = Uuid::uuid4();
                $step->setId($uuid);
                $step->setIsEditMode(true);
                $step->setCreatedAt(new DateTime());
                $step->setUpdatedAt(new DateTime());
                $step->setConfigurator($configurator);
                $step->setLocale($defaultLocale);
                $step->setSite($site);
                $step->setSortIndex($sortIndex);
                $this->entityManager->persist($step);
                $this->entityManager->flush();
                return $this->responseFactory->redirect(Url::to(Page::getCurrentPage(), "edit_step", $uuid), Response::HTTP_TEMPORARY_REDIRECT);
            } else {
                return $this->responseFactory->notFound(t("Invalid Question."));
            }
        } else {
            return $this->responseFactory->notFound(t("Invalid Question."));
        }
    }

    public function add(): Response
    {
        /** @var Site $site */
        /** @noinspection PhpUnhandledExceptionInspection */
        $site = $this->app->make('site')->getActiveSiteForEditing();
        $defaultLocale = "";

        foreach ($site->getLocales() as $localeEntity) {
            if ($localeEntity->getIsDefault()) {
                $defaultLocale = $localeEntity->getLocale();
            }
        }

        $step = new Configurator();
        $uuid = Uuid::uuid4();
        $step->setId($uuid);
        $step->setIsEditMode(true);
        $step->setCreatedAt(new DateTime());
        $step->setUpdatedAt(new DateTime());
        $step->setLocale($defaultLocale);
        $step->setSite($site);
        $this->entityManager->persist($step);
        $this->entityManager->flush();
        return $this->responseFactory->redirect(Url::to(Page::getCurrentPage(), "edit", $uuid), Response::HTTP_TEMPORARY_REDIRECT);
    }

    public function updated()
    {
        $this->set("success", t("The settings has been successfully updated."));
        $this->view();
    }

    public function step_updated($configuratorId = null)
    {
        $this->set("success", t("The settings has been successfully updated."));
        $this->view($configuratorId);
    }

    public function removed()
    {
        $this->set("success", t("The item has been successfully removed."));
        $this->view();
    }

    public function step_removed($configuratorId = null)
    {
        $this->set("success", t("The item has been successfully removed."));
        $this->view($configuratorId);
    }

    public function remove($configuratorId = null)
    {
        if (isset($configuratorId) && Uuid::isValid($configuratorId)) {
            $configurator = $this->getEntityManager()->getRepository(Configurator::class)->findOneBy(["id" => Uuid::fromString($configuratorId)]);

            if ($configurator instanceof Configurator) {
                foreach($configurator->getSteps() as $step) {
                    foreach ($step->getQuestions() as $question) {
                        foreach ($question->getOptions() as $option) {
                            $this->entityManager->remove($option);
                        }

                        $this->entityManager->remove($question);
                    }

                    $this->entityManager->remove($step);
                }

                $this->entityManager->remove($configurator);

                $this->entityManager->flush();

                return $this->responseFactory->redirect(Url::to(Page::getCurrentPage(), "removed"), Response::HTTP_TEMPORARY_REDIRECT);
            }
        }

        return $this->responseFactory->notFound(t("Invalid Step."));
    }

    public function remove_step($stepId = null)
    {
        if (isset($stepId) && Uuid::isValid($stepId)) {
            $step = $this->getEntityManager()->getRepository(Step::class)->findOneBy(["id" => Uuid::fromString($stepId)]);

            if ($step instanceof Step) {
                foreach ($step->getQuestions() as $question) {
                    foreach ($question->getOptions() as $option) {
                        $this->entityManager->remove($option);
                    }

                    $this->entityManager->remove($question);
                }

                $this->entityManager->remove($step);
                $this->entityManager->flush();

                return $this->responseFactory->redirect(Url::to(Page::getCurrentPage(), "step_removed", $step->getConfigurator() instanceof Configurator ? $step->getConfigurator()->getId() : null), Response::HTTP_TEMPORARY_REDIRECT);
            }
        }

        return $this->responseFactory->notFound(t("Invalid Step."));
    }

    public function update_sort_order($stepId = null)
    {
        $r = new UserEditResponse();

        $errorList = new ErrorList();

        if ($this->request->getMethod() === "POST") {
            if ($this->request->request->has("updatedSortOrder")) {
                $updatedSortOrder = $this->request->request->get("updatedSortOrder", []);

                if (is_array($updatedSortOrder)) {
                    foreach ($updatedSortOrder as $updatedSortOrderItem) {
                        if (!isset($updatedSortOrderItem["id"])) {
                            $errorList->add(t("The id is missing."));
                        } else if (!Uuid::isValid($updatedSortOrderItem["id"])) {
                            $errorList->add(t("The id is invalid."));
                        } else if (!isset($updatedSortOrderItem["sortIndex"])) {
                            $errorList->add(t("The sort index is missing."));
                        } else if (!is_numeric($updatedSortOrderItem["sortIndex"])) {
                            $errorList->add(t("The sort index is invalid."));
                        }
                    }
                } else {
                    $errorList->add(t("The payload is invalid."));
                }
            } else {
                $errorList->add(t("The payload is invalid."));
            }

            if (!$errorList->has()) {
                $updatedSortOrder = $this->request->request->get("updatedSortOrder", []);

                foreach ($updatedSortOrder as $updatedSortOrderItem) {
                    if (isset($stepId) && Uuid::isValid($stepId)) {
                        $question = $this->entityManager->getRepository(Question::class)->findOneBy([
                            "id" => Uuid::fromString($updatedSortOrderItem["id"])
                        ]);

                        if ($question instanceof Question) {
                            $question->setSortIndex((int)$updatedSortOrderItem["sortIndex"]);
                            $this->entityManager->persist($question);
                        }
                    } else {
                        $step = $this->entityManager->getRepository(Step::class)->findOneBy([
                            "id" => Uuid::fromString($updatedSortOrderItem["id"])
                        ]);

                        if ($step instanceof Step) {
                            $step->setSortIndex((int)$updatedSortOrderItem["sortIndex"]);
                            $this->entityManager->persist($step);
                        }
                    }
                }

                $this->entityManager->flush();

                $r->setMessage(t("The sort order has been updated successfully."));
                $r->setTitle(t('Sort Order Updated'));
            }
        } else {
            $errorList->add(t("The request method is invalid."));
        }

        $r->setError($errorList);

        return $this->responseFactory->json($r);
    }

    public function edit_step($stepId = null)
    {
        /** @var Site $site */
        /** @noinspection PhpUnhandledExceptionInspection */
        $site = $this->app->make('site')->getActiveSiteForEditing();

        if (isset($stepId) && Uuid::isValid($stepId)) {
            $step = $this->getEntityManager()->getRepository(Step::class)->findOneBy(["id" => Uuid::fromString($stepId)]);

            if ($step instanceof Step) {
                $locales = [];

                if ($this->request->getMethod() === "POST") {
                    $this->formValidator->setData($this->request->request->all());
                    $this->formValidator->addRequiredToken("update_step");

                    if ($this->formValidator->test()) {
                        $step->setName($this->request->request->get("stepName"));
                        $step->setLocale($this->request->request->get("locale"));
                        $step->setUpdatedAt(new DateTime);

                        $this->entityManager->persist($step);
                        $this->entityManager->flush();
                        if (!$this->error->has()) {
                            return $this->responseFactory->redirect(Url::to(Page::getCurrentPage(), "step_updated", $step->getConfigurator() instanceof Configurator ? $step->getConfigurator()->getId() : null), Response::HTTP_TEMPORARY_REDIRECT);
                        }
                    } else {
                        /** @var ErrorList $errorList */
                        $errorList = $this->formValidator->getError();

                        foreach ($errorList->getList() as $error) {
                            $this->error->add($error);
                        }
                    }
                }

                $locales[] = t("*** Please select");

                foreach ($site->getLocales() as $localeEntity) {
                    $locales[$localeEntity->getLocale()] = $localeEntity->getLanguageText();
                }

                $this->set("pageTitle", t("Edit Step"));
                $this->set("locales", $locales);
                $this->set("step", $step);
                $this->render("dashboard/simple_configurator/configurators/steps/edit", "simple_configurator");

                return;
            }
        }

        return $this->responseFactory->notFound(t("Invalid Step."));
    }

    public function edit($configuratorId = null)
    {
        /** @var Site $site */
        /** @noinspection PhpUnhandledExceptionInspection */
        $site = $this->app->make('site')->getActiveSiteForEditing();

        if (isset($configuratorId) && Uuid::isValid($configuratorId)) {
            $configurator = $this->getEntityManager()->getRepository(Configurator::class)->findOneBy(["id" => Uuid::fromString($configuratorId)]);

            if ($configurator instanceof Configurator) {
                $locales = [];

                if ($this->request->getMethod() === "POST") {
                    $this->formValidator->setData($this->request->request->all());
                    $this->formValidator->addRequiredToken("update_configurator");

                    if ($this->formValidator->test()) {
                        $configurator->setName($this->request->request->get("configuratorName"));
                        $configurator->setLocale($this->request->request->get("locale"));
                        $configurator->setUpdatedAt(new DateTime);

                        $this->entityManager->persist($configurator);
                        $this->entityManager->flush();
                        if (!$this->error->has()) {
                            return $this->responseFactory->redirect(Url::to(Page::getCurrentPage(), "updated"), Response::HTTP_TEMPORARY_REDIRECT);
                        }
                    } else {
                        /** @var ErrorList $errorList */
                        $errorList = $this->formValidator->getError();

                        foreach ($errorList->getList() as $error) {
                            $this->error->add($error);
                        }
                    }
                }

                $locales[] = t("*** Please select");

                foreach ($site->getLocales() as $localeEntity) {
                    $locales[$localeEntity->getLocale()] = $localeEntity->getLanguageText();
                }

                $this->set("pageTitle", t("Edit Configurator"));
                $this->set("locales", $locales);
                $this->set("configurator", $configurator);
                $steps = $this->getEntityManager()->getRepository(Step::class)->findBy([
                    "configurator" => $configurator
                ], ["sortIndex" => "ASC"]);
                $this->set("steps", $steps);
                $this->render("dashboard/simple_configurator/configurators/edit", "simple_configurator");

                return;
            }
        }

        return $this->responseFactory->notFound(t("Invalid Step."));
    }

    public function view($configuratorId = null)
    {
        $configurators = $this->getEntityManager()->getRepository(Configurator::class)->findBy([], []);
        $this->set("configurators", $configurators);

        if (isset($configuratorId) && Uuid::isValid($configuratorId)) {
            $configurator = $this->getEntityManager()->getRepository(Configurator::class)->findOneBy(["id" => Uuid::fromString($configuratorId)]);

            if ($configurator instanceof Configurator) {
                /** @var Site $site */
                /** @noinspection PhpUnhandledExceptionInspection */
                $site = $this->app->make('site')->getActiveSiteForEditing();

                $locales[] = t("*** Please select");

                foreach ($site->getLocales() as $localeEntity) {
                    $locales[$localeEntity->getLocale()] = $localeEntity->getLanguageText();
                }

                $this->set("configurator", $configurator);
                $steps = $this->getEntityManager()->getRepository(Step::class)->findBy([
                    "configurator" => $configurator
                ], ["sortIndex" => "ASC"]);
                $this->set("steps", $steps);
                $this->set("locales", $locales);

                $this->render("dashboard/simple_configurator/configurators/edit", "simple_configurator");
            }
        }

    }
}