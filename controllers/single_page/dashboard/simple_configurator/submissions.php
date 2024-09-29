<?php /** @noinspection PhpUnused */

namespace Concrete\Package\SimpleConfigurator\Controller\SinglePage\Dashboard\SimpleConfigurator;

use Bitter\SimpleConfigurator\Attribute\Category\ConfiguratorCategory;
use Bitter\SimpleConfigurator\Entity\Attribute\Key\SubmissionKey;
use Bitter\SimpleConfigurator\Entity\Configurator\Submission;
use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Http\Response;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Url;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Submissions extends DashboardPageController
{
    protected ResponseFactoryInterface $responseFactory;

    public function on_start()
    {
        parent::on_start();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->responseFactory = $this->app->make(ResponseFactoryInterface::class);
    }

    public function removed()
    {
        $this->set("success", t("The item has been successfully removed."));
        $this->view();
    }

    /** @noinspection PhpInconsistentReturnPointsInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function export($submissionId = null)
    {
        if (isset($submissionId) && Uuid::isValid($submissionId)) {
            $submission = $this->getEntityManager()->getRepository(Submission::class)->findOneBy(["id" => Uuid::fromString($submissionId)]);

            if ($submission instanceof Submission) {

                $lines = [];

                /** @noinspection PhpUnhandledExceptionInspection */
                /** @noinspection DuplicatedCode */
                $service = $this->app->make(CategoryService::class);
                $categoryEntity = $service->getByHandle('configurator');
                /** @var ConfiguratorCategory $category */
                $category = $categoryEntity->getController();
                $setManager = $category->getSetManager();

                foreach ($setManager->getUnassignedAttributeKeys() as $ak) {
                    if ($ak instanceof SubmissionKey) {
                        $lines[] = [
                            $ak->getAttributeKeyName(),
                            "",
                            $submission->getAttribute($ak->getAttributeKeyHandle())
                        ];
                    }
                }


                foreach ($submission->getPositions() as $position) {
                    $lines[] = [
                        $position->getLabel(),
                        $position->getValue(),
                        $position->getPrice()
                    ];
                }

                $response = new StreamedResponse();
                $response->headers->set('Cache-Control', 'no-cache');
                $response->headers->set('Content-Type', 'application/force-download');
                $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    "export.csv"
                ));

                $response->setCallback(function () use ($lines) {
                    $handle = fopen('php://output', 'w+');

                    fwrite($handle, chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF')));

                    foreach ($lines as $line) {
                        fputcsv($handle, $line, ';');
                    }

                    fclose($handle);
                });

                return $response;
            }
        }

        return $this->responseFactory->notFound(t("Invalid Step."));
    }

    /** @noinspection PhpInconsistentReturnPointsInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function remove($submissionId = null)
    {
        if (isset($submissionId) && Uuid::isValid($submissionId)) {
            $submission = $this->getEntityManager()->getRepository(Submission::class)->findOneBy(["id" => Uuid::fromString($submissionId)]);

            if ($submission instanceof Submission) {
                /** @noinspection SqlDialectInspection */
                /** @noinspection SqlNoDataSourceInspection */
                /** @noinspection PhpUnhandledExceptionInspection */
                $this->entityManager->getConnection()->executeQuery("SET FOREIGN_KEY_CHECKS = 0");

                $this->entityManager->remove($submission);
                $this->entityManager->flush();

                /** @noinspection PhpClassConstantAccessedViaChildClassInspection */
                return $this->responseFactory->redirect(Url::to(Page::getCurrentPage(), "removed"), Response::HTTP_TEMPORARY_REDIRECT);
            }
        }

        return $this->responseFactory->notFound(t("Invalid Step."));
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function details($submissionId = null)
    {
        if (isset($submissionId) && Uuid::isValid($submissionId)) {
            $submission = $this->getEntityManager()->getRepository(Submission::class)->findOneBy(["id" => Uuid::fromString($submissionId)]);

            if ($submission instanceof Submission) {
                $this->set("pageTitle", t("View Submission"));
                $this->set("submission", $submission);
                $this->render("dashboard/simple_configurator/submission/details", "simple_configurator");
                return;
            }
        }

        return $this->responseFactory->notFound(t("Invalid Step."));
    }

    public function view()
    {
        $submissions = $this->entityManager->getRepository(Submission::class)->findBy([], ["createdAt" => "DESC"]);
        $this->set("submissions", $submissions);
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection DuplicatedCode */
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
}