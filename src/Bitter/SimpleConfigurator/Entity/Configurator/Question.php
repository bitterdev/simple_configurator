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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="ConfiguratorQuestion")
 */
class Question implements JsonSerializable
{
    use PackageTrait;

    /**
     * @var UuidInterface|string|null
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=RespectfulUuidGenerator::class)
     */
    protected $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $sortIndex = 1;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $tooltip;

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
     * @var ArrayCollection|QuestionOption[]
     * @ORM\OneToMany(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\QuestionOption", mappedBy="question", orphanRemoval=true)
     */
    protected $options;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isRequired = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isEditMode = false;

    /**
     * @var Step|null
     * @ORM\ManyToOne(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\Step", inversedBy="questions")
     * @ORM\JoinColumn(name="stepId", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $step;

    /**
     * @var Question|null
     * @ORM\ManyToOne(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\Question")
     * @ORM\JoinColumn(name="referenceQuestionId", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $reference;

    public function __construct(UuidInterface $id = null)
    {
        $this->id = ($id ?: Uuid::uuid4());
        $this->options = new ArrayCollection();
    }

    /**
     * @return UuidInterface|string|null
     */
    public function getId(): UuidInterface|string|null
    {
        return $this->id;
    }

    /**
     * @param UuidInterface|string|null $id
     * @return Question
     */
    public function setId(UuidInterface|string|null $id): Question
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Question
     */
    public function setName(?string $name): Question
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function hasDescription(): bool
    {
        return strlen($this->getDescription()) > 0;
    }

    public function hasTooltip(): bool
    {
        return strlen($this->getTooltip()) > 0;
    }

    /**
     * @param string|null $description
     * @return Question
     */
    public function setDescription(?string $description): Question
    {
        $this->description = $description;
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
     * @return Question
     */
    public function setCreatedAt(DateTime $createdAt): Question
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
     * @return Question
     */
    public function setUpdatedAt(DateTime $updatedAt): Question
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return QuestionOption[]|Collection
     */
    public function getOptions(): Collection|array
    {
        return $this->options;
    }

    public function hasImageOptions(): bool
    {
        if ($this->getReference() instanceof Question) {
            return false;
        } else {
            foreach ($this->getOptions() as $option) {
                if (!$option->getImage() instanceof File) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getOptionList(): array
    {
        $optionList = [];

        $optionList[] = t("*** Please select");

        if ($this->getReference() instanceof Question) {
            $optionList["no"] = t("No");
            $optionList["yes"] = t("Yes");
        } else {
            foreach ($this->getOptions() as $option) {
                $optionList[(string)$option->getId()] = $option->getValue();
            }
        }

        return $optionList;
    }

    /**
     * @param QuestionOption[]|Collection $options
     * @return Question
     */
    public function setOptions(Collection|array $options): Question
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @param bool $isRequired
     * @return Question
     */
    public function setIsRequired(bool $isRequired): Question
    {
        $this->isRequired = $isRequired;
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
     * @return Question
     */
    public function setSortIndex(int $sortIndex): Question
    {
        $this->sortIndex = $sortIndex;
        return $this;
    }

    /**
     * @return Step|null
     */
    public function getStep(): ?Step
    {
        return $this->step;
    }

    /**
     * @param Step|null $step
     * @return Question
     */
    public function setStep(?Step $step): Question
    {
        $this->step = $step;
        return $this;
    }

    /**
     * @return Question|null
     */
    public function getReference(): ?Question
    {
        return $this->reference;
    }

    /**
     * @param Question|null $reference
     * @return Question
     */
    public function setReference(?Question $reference): Question
    {
        $this->reference = $reference;
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
     * @return Question
     */
    public function setIsEditMode(bool $isEditMode): Question
    {
        $this->isEditMode = $isEditMode;
        return $this;
    }

    public function getDisplayName(): string
    {
        if (strlen($this->getName()) > 0) {
            return $this->getName();
        } else {
            return t("(Missing Name, ID: %s)", $this->getId());
        }
    }

    /**
     * @return string|null
     */
    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }

    /**
     * @param string|null $tooltip
     * @return Question
     */
    public function setTooltip(?string $tooltip): Question
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    #[ArrayShape(["id" => "string", "displayName" => "string", "options" => "mixed"])] public function jsonSerialize(): array
    {
        return [
            "id" => $this->getId(),
            "displayName" => $this->getDisplayName(),
            "options" => $this->getOptions()->toArray()
        ];
    }
}