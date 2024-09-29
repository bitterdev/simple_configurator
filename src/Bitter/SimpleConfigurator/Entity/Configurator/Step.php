<?php /** @noinspection PhpUnusedAliasInspection */
/** @noinspection PhpUnused */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Bitter\SimpleConfigurator\Entity\Configurator;

use Bitter\SimpleConfigurator\Doctrine\Uuid\RespectfulUuidGenerator;
use Bitter\SimpleConfigurator\Entity\Configurator;
use Concrete\Core\Attribute\AttributeKeyInterface;
use Concrete\Core\Attribute\AttributeValueInterface;
use Concrete\Core\Attribute\ObjectInterface;
use Concrete\Core\Attribute\ObjectTrait;
use Concrete\Core\Entity\PackageTrait;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\View\View;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="ConfiguratorStep")
 */
class Step implements JsonSerializable
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
    protected $locale;

    /**
     * @ORM\ManyToOne(targetEntity="\Concrete\Core\Entity\Site\Site")
     * @ORM\JoinColumn(name="siteID", referencedColumnName="siteID", onDelete="CASCADE")
     *
     * @var Site|null
     */
    protected $site = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isEditMode = false;

    /**
     * @var ArrayCollection|Question[]
     * @ORM\OneToMany(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\Question", mappedBy="step", orphanRemoval=true)
     */
    protected $questions;

    /**
     * @var Configurator|null
     * @ORM\ManyToOne(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator", inversedBy="steps")
     * @ORM\JoinColumn(name="configuratorId", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $configurator;

    public function __construct(UuidInterface $id = null)
    {
        $this->id = ($id ?: Uuid::uuid4());
        $this->questions = new ArrayCollection();
    }

    /**
     * @return Configurator|null
     */
    public function getConfigurator(): ?Configurator
    {
        return $this->configurator;
    }

    /**
     * @param Configurator|null $configurator
     * @return Step
     */
    public function setConfigurator(?Configurator $configurator): Step
    {
        $this->configurator = $configurator;
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
     * @return Step
     */
    public function setId(UuidInterface|string $id): Step
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
     * @return Step
     */
    public function setName(?string $name): Step
    {
        $this->name = $name;
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
     * @return Step
     */
    public function setCreatedAt(DateTime $createdAt): Step
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
     * @return Step
     */
    public function setUpdatedAt(DateTime $updatedAt): Step
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string|null $locale
     * @return Step
     */
    public function setLocale(?string $locale): Step
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return Site|null
     */
    public function getSite(): ?Site
    {
        return $this->site;
    }

    /**
     * @param Site|null $site
     * @return Step
     */
    public function setSite(?Site $site): Step
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return Question[]|Collection
     */
    public function getQuestions(): array|Collection
    {
        return $this->questions;
    }

    /**
     * @param Question[]|Collection $questions
     * @return Step
     */
    public function setQuestions(array|Collection $questions): Step
    {
        $this->questions = $questions;
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
     * @return Step
     */
    public function setIsEditMode(bool $isEditMode): Step
    {
        $this->isEditMode = $isEditMode;
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
     * @return Step
     */
    public function setSortIndex(int $sortIndex): Step
    {
        $this->sortIndex = $sortIndex;
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

    public function transformToPositionValues(array $args): array
    {
        $positionValues = [];

        foreach ($this->getQuestions() as $question) {
            if (isset($args["question"]) &&
                is_array($args["question"]) &&
                isset($args["question"][(string)$question->getId()])) {

                $submittedValue = $args["question"][(string)$question->getId()];

                $positionValues[(string)$question->getId()] = $submittedValue;
            }
        }

        return $positionValues;
    }

    public function validate(array $args): ErrorList
    {
        $errorList = new ErrorList();

        foreach ($this->getQuestions() as $question) {
            if ($question->isRequired()) {
                $allowedOptionValues = [];

                if ($question->getReference() instanceof Question) {
                    $allowedOptionValues = ["no", "yes"];
                } else {
                    foreach ($question->getOptions() as $option) {
                        $allowedOptionValues[] = $option->getId();
                    }
                }

                $validAnswerSubmitted = false;

                if (isset($args["question"]) &&
                    is_array($args["question"]) &&
                    isset($args["question"][(string)$question->getId()])) {

                    $submittedValue = $args["question"][(string)$question->getId()];

                    if (in_array($submittedValue, $allowedOptionValues)) {
                        $validAnswerSubmitted = true;
                    }
                }

                if (!$validAnswerSubmitted) {
                    $errorList->add(t("You need to define a valid value for %s.", $question->getName()));
                }
            }
        }

        return $errorList;
    }

    public function render(array $storedPositionValues = [])
    {
        try {
            View::element("configurator/step", [
                "step" => $this,
                "storedPositionValues" => $storedPositionValues
            ], "simple_configurator");
        } catch (Exception) {
            // Ignore
        }
    }

    #[ArrayShape(["id" => "string", "displayName" => "string", "questions" => "mixed"])] public function jsonSerialize(): array
    {
        return [
            "id" => $this->getId(),
            "displayName" => $this->getDisplayName(),
            "questions" => $this->getQuestions()->toArray()
        ];
    }
}