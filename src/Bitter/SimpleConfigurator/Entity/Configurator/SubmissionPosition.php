<?php /** @noinspection PhpUnusedAliasInspection */
/** @noinspection PhpUnused */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Bitter\SimpleConfigurator\Entity\Configurator;

use Bitter\SimpleConfigurator\Doctrine\Uuid\RespectfulUuidGenerator;
use Bitter\SimpleConfigurator\Transformer\MoneyTransformer;
use Concrete\Core\Attribute\AttributeKeyInterface;
use Concrete\Core\Attribute\AttributeValueInterface;
use Concrete\Core\Attribute\ObjectInterface;
use Concrete\Core\Attribute\ObjectTrait;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Entity\PackageTrait;
use Concrete\Core\Support\Facade\Application;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="ConfiguratorSubmissionPosition")
 */
class SubmissionPosition implements JsonSerializable
{
    use PackageTrait;

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
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected $label;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected $value;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=14, scale=2)
     */
    protected $price = 0;

    /**
     * @var Submission|null
     * @ORM\ManyToOne(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\Submission", inversedBy="options")
     * @ORM\JoinColumn(name="submissionId", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $submission;

    public function __construct(UuidInterface $id = null)
    {
        $this->id = ($id ?: Uuid::uuid4());
    }

    /**
     * @return UuidInterface|string
     */
    public function getId(): UuidInterface|string
    {
        return $this->id;
    }

    /**
     * @param UuidInterface|string $id
     * @return SubmissionPosition
     */
    public function setId(UuidInterface|string $id): SubmissionPosition
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
     * @return SubmissionPosition
     */
    public function setCreatedAt(DateTime $createdAt): SubmissionPosition
    {
        $this->createdAt = $createdAt;
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
     * @return SubmissionPosition
     */
    public function setUpdatedAt(DateTime $updatedAt): SubmissionPosition
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     * @return SubmissionPosition
     */
    public function setLabel(?string $label): SubmissionPosition
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return SubmissionPosition
     */
    public function setValue(?string $value): SubmissionPosition
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return float|int
     */
    public function getPrice(): float|int
    {
        return $this->price;
    }

    /**
     * @param float|int $price
     * @return SubmissionPosition
     */
    public function setPrice(float|int $price): SubmissionPosition
    {
        $this->price = $price;
        return $this;
    }

    public function getPriceDisplayValue(): string
    {
        $app = Application::getFacadeApplication();
        /** @var MoneyTransformer $moneyTransformer */
        /** @noinspection PhpUnhandledExceptionInspection */
        $moneyTransformer = $app->make(MoneyTransformer::class);
        return $moneyTransformer->transform($this->getPrice());
    }

    /**
     * @return Submission|null
     */
    public function getSubmission(): ?Submission
    {
        return $this->submission;
    }

    /**
     * @param Submission|null $submission
     * @return SubmissionPosition
     */
    public function setSubmission(?Submission $submission): SubmissionPosition
    {
        $this->submission = $submission;
        return $this;
    }

    #[ArrayShape(["id" => "\Ramsey\Uuid\UuidInterface|string", "label" => "null|string", "value" => "null|string", "price" => "float|int"])] public function jsonSerialize(): array
    {
        return [
            "id" => $this->getId(),
            "label" => $this->getLabel(),
            "value" => $this->getValue(),
            "price" => $this->getPrice()
        ];
    }
}