<?php

namespace Bitter\SimpleConfigurator\Attribute\Category;

use Bitter\SimpleConfigurator\Entity\Attribute\Key\SubmissionKey;
use Bitter\SimpleConfigurator\Entity\Attribute\Value\SubmissionValue;
use Bitter\SimpleConfigurator\Submission\SubmissionInfo;
use Concrete\Core\Attribute\Category\AbstractStandardCategory;
use Concrete\Core\Entity\Attribute\Key\Key;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\Persistence\ObjectRepository;
use JetBrains\PhpStorm\ArrayShape;

class ConfiguratorCategory extends AbstractStandardCategory
{
    public function createAttributeKey(): SubmissionKey
    {
        return new SubmissionKey();
    }

    public function getIndexedSearchTable(): string
    {
        return 'SubmissionSearchIndexAttributes';
    }

    /**
     * @param SubmissionInfo $mixed
     * @return string
     */
    public function getIndexedSearchPrimaryKeyValue($mixed): string
    {
        return $mixed->getSubmissionId();
    }

    #[ArrayShape(['columns' => "array[]", 'primary' => "string[]"])] public function getSearchIndexFieldDefinition(): array
    {
        return [
            'columns' => [
                [
                    'name' => 'submissionId',
                    'type' => 'string',
                    'options' => ['unsigned' => false, 'notnull' => true]
                ],
            ],
            'primary' => ['submissionId']
        ];
    }

    /**
     * @throws NotSupported
     */
    public function getAttributeKeyRepository(): EntityRepository|ObjectRepository
    {
        return $this->entityManager->getRepository(SubmissionKey::class);
    }

    /**
     * @throws NotSupported
     */
    public function getAttributeValueRepository(): EntityRepository|ObjectRepository
    {
        return $this->entityManager->getRepository(SubmissionValue::class);
    }

    /**
     * @throws NotSupported
     */
    public function getAttributeValues($object): array
    {
        return $this->getAttributeValueRepository()->findBy([
            'submission' => $object
        ]);
    }

    /**
     * @throws NotSupported
     */
    public function getAttributeValue(Key $key, $object)
    {
        return $this->getAttributeValueRepository()->findOneBy([
            'submission' => $object,
            'attribute_key' => $key
        ]);
    }

}
