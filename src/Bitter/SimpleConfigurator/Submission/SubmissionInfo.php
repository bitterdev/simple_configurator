<?php /** @noinspection PhpUnused */

namespace Bitter\SimpleConfigurator\Submission;

use Bitter\SimpleConfigurator\Attribute\Category\ConfiguratorCategory;
use Bitter\SimpleConfigurator\Attribute\Key\SubmissionKey;
use Bitter\SimpleConfigurator\Entity\Attribute\Value\SubmissionValue;
use Bitter\SimpleConfigurator\Entity\Configurator\Submission;
use Concrete\Core\Attribute\Category\CategoryInterface;
use Concrete\Core\Attribute\ObjectInterface as AttributeObjectInterface;
use Concrete\Core\Attribute\ObjectTrait;
use Concrete\Core\Foundation\ConcreteObject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;

class SubmissionInfo extends ConcreteObject implements AttributeObjectInterface
{
    use ObjectTrait;

    protected ConfiguratorCategory $attributeCategory;
    protected EntityManagerInterface $entityManager;
    /** @var Submission */
    protected Submission $entity;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConfiguratorCategory   $attributeCategory
    )
    {
        $this->entityManager = $entityManager;
        $this->attributeCategory = $attributeCategory;
    }

    /**
     * @return string
     */
    public function getSubmissionId(): string
    {
        return $this->getEntity()->getId();
    }

    /**
     * @return Submission
     */
    public function getEntity(): Submission
    {
        return $this->entity;
    }

    /**
     * @param Submission $entity
     * @return SubmissionInfo
     */
    public function setEntity(Submission $entity): SubmissionInfo
    {
        $this->entity = $entity;
        return $this;
    }

    public function getObjectAttributeCategory(): ConfiguratorCategory|CategoryInterface
    {
        return $this->attributeCategory;
    }

    public function getAttributeValueObject($ak, $createIfNotExists = false)
    {
        if (!is_object($ak)) {
            $ak = SubmissionKey::getByHandle($ak);
        }

        if ($ak instanceof \Bitter\SimpleConfigurator\Entity\Attribute\Key\SubmissionKey) {
            try {
                $value = $this->getObjectAttributeCategory()->getAttributeValue($ak, $this->entity);
            } catch (NotSupported) {
                $value = null;
            }
        } else {
            $value = null;
        }

        if ($value === null && $createIfNotExists) {
            $value = new SubmissionValue();
            $value->setSubmission($this->entity);
            $value->setAttributeKey($ak);
        }

        return $value;
    }

    /**
     * @param \Bitter\SimpleConfigurator\Entity\Attribute\Key\SubmissionKey[] $attributes
     */
    public function saveUserAttributesForm(array $attributes)
    {
        foreach ($attributes as $uak) {
            $controller = $uak->getController();
            $value = $controller->createAttributeValueFromRequest();
            $this->setAttribute($uak, $value, false);
        }
    }
}