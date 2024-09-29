<?php /** @noinspection PhpUnusedAliasInspection */

/** @noinspection PhpUnused */

namespace Bitter\SimpleConfigurator\Entity;

use Bitter\SimpleConfigurator\Entity\Configurator\Step;
use Concrete\Core\Entity\PackageTrait;
use Concrete\Core\Entity\Site\Site;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;
use Doctrine\ORM\PersistentCollection;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use Bitter\SimpleConfigurator\Doctrine\Uuid\RespectfulUuidGenerator;

/**
 * @ORM\Entity
 * @ORM\Table(name="Configurator")
 */
class Configurator implements JsonSerializable
{
    use PackageTrait;

    /**
     * @var string|UuidInterface
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=RespectfulUuidGenerator::class)
     */
    protected string|UuidInterface $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $name;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected DateTime $createdAt;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime")
     */
    protected ?DateTime $updatedAt;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $locale;

    /**
     * @ORM\ManyToOne(targetEntity="\Concrete\Core\Entity\Site\Site")
     * @ORM\JoinColumn(name="siteID", referencedColumnName="siteID", onDelete="CASCADE")
     *
     * @var Site|null
     */
    protected ?Site $site = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected bool $isEditMode = false;

    /**
     * @var ArrayCollection|PersistentCollection|Step[]
     * @ORM\OneToMany(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\Step", mappedBy="configurator", orphanRemoval=true)
     */
    protected array|ArrayCollection|PersistentCollection $steps;

    public function __construct(UuidInterface $id = null)
    {
        $this->id = ($id ?: Uuid::uuid4());
        $this->steps = new ArrayCollection();
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
     * @return Configurator
     */
    public function setId(UuidInterface|string $id): Configurator
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
     * @return Configurator
     */
    public function setName(?string $name): Configurator
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
     * @return Configurator
     */
    public function setCreatedAt(DateTime $createdAt): Configurator
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
     * @return Configurator
     */
    public function setUpdatedAt(DateTime $updatedAt): Configurator
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
     * @return Configurator
     */
    public function setLocale(?string $locale): Configurator
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
     * @return Configurator
     */
    public function setSite(?Site $site): Configurator
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return Step[]|Collection|PersistentCollection
     */
    public function getSteps(): array|Collection|PersistentCollection
    {
        return $this->steps;
    }

    /**
     * @param Step[]|Collection|PersistentCollection $steps
     * @return Configurator
     */
    public function setSteps(array|Collection|PersistentCollection $steps): Configurator
    {
        $this->steps = $steps;
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
     * @return Configurator
     */
    public function setIsEditMode(bool $isEditMode): Configurator
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

    #[ArrayShape(["id" => "string", "displayName" => "string", "steps" => "mixed"])] public function jsonSerialize(): array
    {
        return [
            "id" => $this->getId(),
            "displayName" => $this->getDisplayName(),
            "steps" => $this->getSteps()->toArray()
        ];
    }
}