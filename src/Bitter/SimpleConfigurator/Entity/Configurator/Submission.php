<?php /** @noinspection PhpUnusedAliasInspection */
/** @noinspection PhpUnused */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Bitter\SimpleConfigurator\Entity\Configurator;

use Bitter\SimpleConfigurator\Attribute\Category\ConfiguratorCategory;
use Bitter\SimpleConfigurator\Entity\Attribute\Key\SubmissionKey;
use Bitter\SimpleConfigurator\Entity\Attribute\Value\SubmissionValue;
use Bitter\SimpleConfigurator\Transformer\MoneyTransformer;
use Concrete\Core\Attribute\AttributeKeyInterface;
use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Attribute\ObjectInterface;
use Concrete\Core\Attribute\ObjectTrait;
use Concrete\Core\Entity\PackageTrait;
use Bitter\SimpleConfigurator\Doctrine\Uuid\RespectfulUuidGenerator;
use Concrete\Core\Support\Facade\Application;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use HtmlObject\Element;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="ConfiguratorSubmission")
 */
class Submission implements ObjectInterface
{
    use PackageTrait;
    use ObjectTrait;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=RespectfulUuidGenerator::class)
     */
    protected $id;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var ArrayCollection|SubmissionPosition[]
     * @ORM\OneToMany(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\SubmissionPosition", mappedBy="submission", orphanRemoval=true)
     */
    protected $positions;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    public function __construct(UuidInterface $id = null)
    {
        $this->id = ($id ?: Uuid::uuid4());
        $this->positions = new ArrayCollection();
    }

    /**
     * @return SubmissionPosition[]|Collection
     */
    public function getPositions(): array|Collection
    {
        return $this->positions;
    }

    public function getTotal(): float|int
    {
        $total = 0;

        foreach ($this->getPositions() as $position) {
            $total += $position->getPrice();
        }

        return $total;
    }

    public function getTotalDisplayValue(): string
    {
        $app = Application::getFacadeApplication();
        /** @var MoneyTransformer $moneyTransformer */
        /** @noinspection PhpUnhandledExceptionInspection */
        $moneyTransformer = $app->make(MoneyTransformer::class);
        return $moneyTransformer->transform($this->getTotal());
    }

    public function getAttributesTable(): string
    {
        $tbody = new Element("tbody");

        $app = Application::getFacadeApplication();
        /** @var CategoryService $service */
        /** @noinspection PhpUnhandledExceptionInspection */
        $service = $app->make(CategoryService::class);
        $categoryEntity = $service->getByHandle('configurator');
        /** @var ConfiguratorCategory $category */
        $category = $categoryEntity->getController();
        $setManager = $category->getSetManager();


        foreach ($setManager->getUnassignedAttributeKeys() as $ak) {
            /** @var SubmissionKey $ak */
            $tbody->appendChild(
                (new Element("tr"))->appendChild(
                    (new Element("td"))->setValue((string)$ak->getAttributeKeyName())
                )->appendChild(
                    (new Element("td"))->setValue((string)$this->getAttribute($ak->getAttributeKeyHandle()))->setAttribute("align", "right")->addClass("text-end")
                )
            );
        }

        return (string)(new Element("table"))->appendChild(
            (new Element("thead"))->appendChild(
                (new Element("tr"))->appendChild(
                    (new Element("th"))->setValue(t("Name"))
                )->appendChild(
                    (new Element("th"))->setValue(t("Value"))->setAttribute("align", "right")->addClass("text-end")
                )
            )
        )->appendChild(
            $tbody
        )->addClass("table table-striped")->setAttribute("border", "0")->render();
    }

    public function getPriceTable(): string
    {
        $tbody = new Element("tbody");

        foreach ($this->getPositions() as $position) {
            $tbody->appendChild(
                (new Element("tr"))->appendChild(
                    (new Element("td"))->setValue($position->getLabel())
                )->appendChild(
                    (new Element("td"))->setValue($position->getValue())
                )->appendChild(
                    (new Element("td"))->setValue($position->getPriceDisplayValue())->setAttribute("align", "right")->addClass("text-end")
                )
            );
        }

        return (string)(new Element("table"))->appendChild(
            (new Element("thead"))->appendChild(
                (new Element("tr"))->appendChild(
                    (new Element("th"))->setValue(t("Description"))
                )->appendChild(
                    (new Element("th"))->setValue(t("Value"))
                )->appendChild(
                    (new Element("th"))->setValue(t("Price"))->setAttribute("align", "right")->addClass("text-end")
                )
            )
        )->appendChild(
            $tbody
        )->appendChild(
            (new Element("tfoot"))->appendChild(
                (new Element("tr"))->appendChild(
                    (new Element("td"))->setValue(t("Total"))->setAttribute("colspan", 2)
                )->appendChild(
                    (new Element("td"))->setValue($this->getTotalDisplayValue())->setAttribute("align", "right")->addClass("text-end")
                )
            )->addClass("fw-bold")
        )->addClass("table table-striped")->setAttribute("border", "0")->render();
    }

    /**
     * @param SubmissionPosition[]|Collection $positions
     * @return Submission
     */
    public function setPositions(array|Collection $positions): Submission
    {
        $this->positions = $positions;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     * @return Submission
     */
    public function setUpdatedAt(DateTime $updatedAt): Submission
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): UuidInterface|string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Submission
     */
    public function setId(UuidInterface|string $id): Submission
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return Submission
     */
    public function setCreatedAt(DateTime $createdAt): Submission
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getAttributeValueObject($ak, $createIfNotExists = false)
    {
        if (!($ak instanceof AttributeKeyInterface)) {
            $ak = $ak ? $this->getObjectAttributeCategory()->getAttributeKeyByHandle((string)$ak) : null;
        }

        if ($ak === null) {
            $result = null;
        } else {
            try {
                $result = $this->getObjectAttributeCategory()->getAttributeValue($ak, $this);
            } catch (NotSupported) {
                $result = null;
            }

            if ($result === null && $createIfNotExists) {
                $result = new SubmissionValue();
                $result->setSubmission($this);
                $result->setAttributeKey($ak);
            }
        }

        return $result;
    }

    public function getObjectAttributeCategory(): ConfiguratorCategory
    {
        $app = Application::getFacadeApplication();
        /** @noinspection PhpUnhandledExceptionInspection */
        return $app->make(ConfiguratorCategory::class);
    }
}