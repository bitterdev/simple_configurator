<?php /** @noinspection PhpUnused */

namespace Bitter\SimpleConfigurator\API\V1;

use Bitter\SimpleConfigurator\Entity\Configurator\Question;
use Bitter\SimpleConfigurator\Entity\Configurator\Step;
use Concrete\Core\Application\EditResponse;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Http\ResponseFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Configurator
{
    protected EntityManagerInterface $entityManager;
    protected ResponseFactoryInterface $responseFactory;

    public function __construct(
        EntityManagerInterface   $entityManager,
        ResponseFactoryInterface $responseFactory
    )
    {
        $this->entityManager = $entityManager;
        $this->responseFactory = $responseFactory;
    }

    public function getQuestions(?string $stepId = null): Response
    {
        $editResponse = new EditResponse();
        $errorList = new ErrorList();
        if (isset($stepId) && Uuid::isValid($stepId)) {
            $step = $this->entityManager->getRepository(Step::class)->findOneBy(["id" => Uuid::fromString($stepId)]);
            if ($step instanceof Step) {
                $questions = $this->entityManager->getRepository(Question::class)->findBy(["step" => $step], ["sortIndex" => "ASC"]);
                $editResponse->setAdditionalDataAttribute("questions", $questions);
                $editResponse->setError($errorList);
                return new JsonResponse($editResponse);
            }
        }
        return $this->responseFactory->notFound(t("Invalid Step."));
    }

    public function getSteps(): Response
    {
        $editResponse = new EditResponse();
        $errorList = new ErrorList();
        $steps = $this->entityManager->getRepository(Step::class)->findBy([], ["sortIndex" => "ASC"]);
        $editResponse->setAdditionalDataAttribute("steps", $steps);
        $editResponse->setError($errorList);
        return new JsonResponse($editResponse);
    }
}