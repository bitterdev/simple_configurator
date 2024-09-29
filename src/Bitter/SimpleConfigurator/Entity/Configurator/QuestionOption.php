<?php /** @noinspection PhpUnusedAliasInspection */
/** @noinspection PhpUnused */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Bitter\SimpleConfigurator\Entity\Configurator;

use Bitter\SimpleConfigurator\Doctrine\Uuid\RespectfulUuidGenerator;
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
 * @ORM\Table(name="ConfiguratorQuestionOption")
 */
class QuestionOption implements JsonSerializable
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
     * @var QuestionOption|null
     * @ORM\ManyToOne(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\QuestionOption")
     * @ORM\JoinColumn(name="referenceOptionId", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $referencedOption;

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
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $sortIndex = 1;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected $value;

    /**
     * @var File|null
     * @ORM\ManyToOne(targetEntity="\Concrete\Core\Entity\File\File")
     * @ORM\JoinColumn(name="fID", referencedColumnName="fID", onDelete="SET NULL")
     */
    protected $image = null;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=14, scale=2)
     */
    protected $price = 0;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isEditMode = false;

    /**
     * @var Question|null
     * @ORM\ManyToOne(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\Question", inversedBy="options")
     * @ORM\JoinColumn(name="questionId", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $question;

    public function __construct(UuidInterface $id = null)
    {
        $this->id = ($id ?: Uuid::uuid4());
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
     * @return QuestionOption
     */
    public function setId(UuidInterface|string $id): QuestionOption
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
     * @return QuestionOption
     */
    public function setCreatedAt(DateTime $createdAt): QuestionOption
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
     * @return QuestionOption
     */
    public function setUpdatedAt(DateTime $updatedAt): QuestionOption
    {
        $this->updatedAt = $updatedAt;
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
     * @return QuestionOption
     */
    public function setValue(?string $value): QuestionOption
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return File|null
     */
    public function getImage(): ?File
    {
        return $this->image;
    }

    /**
     * @param File|null $image
     * @return QuestionOption
     */
    public function setImage(?File $image): QuestionOption
    {
        $this->image = $image;
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
     * @return QuestionOption
     */
    public function setPrice(float|int $price): QuestionOption
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return Question|null
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * @param Question|null $question
     * @return QuestionOption
     */
    public function setQuestion(?Question $question): QuestionOption
    {
        $this->question = $question;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortIndex(): int
    {
        return $this->sortIndex;
    }

    /**
     * @param int $sortIndex
     * @return QuestionOption
     */
    public function setSortIndex(int $sortIndex): QuestionOption
    {
        $this->sortIndex = $sortIndex;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEditMode(): bool
    {
        return $this->isEditMode;
    }

    /**
     * @param bool $isEditMode
     * @return QuestionOption
     */
    public function setIsEditMode(bool $isEditMode): QuestionOption
    {
        $this->isEditMode = $isEditMode;
        return $this;
    }

    /**
     * @return QuestionOption|null
     */
    public function getReferencedOption(): ?QuestionOption
    {
        return $this->referencedOption;
    }

    /**
     * @param QuestionOption|null $referencedOption
     * @return QuestionOption
     */
    public function setReferencedOption(?QuestionOption $referencedOption): QuestionOption
    {
        $this->referencedOption = $referencedOption;
        return $this;
    }

    #[ArrayShape(["id" => "string", "value" => "null|string", "price" => "float|int"])] public function jsonSerialize(): array
    {
        return [
            "id" => $this->getId(),
            "value" => $this->getValue(),
            "price" => $this->getPrice()
        ];
    }
}