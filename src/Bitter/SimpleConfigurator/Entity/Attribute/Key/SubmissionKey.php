<?php /** @noinspection PhpUnusedAliasInspection */

namespace Bitter\SimpleConfigurator\Entity\Attribute\Key;

use Concrete\Core\Entity\Attribute\Key\Key;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SubmissionAttributeKeys")
 */
class SubmissionKey extends Key
{
    public function getAttributeKeyCategoryHandle(): string
    {
        return 'configurator';
    }
}
