<?php /** @noinspection PhpUnused */

/** @noinspection PhpUnusedAliasInspection */

namespace Bitter\SimpleConfigurator\Entity\Attribute\Value;

use Bitter\SimpleConfigurator\Entity\Configurator\Submission;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Entity\Attribute\Value\AbstractValue;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="SubmissionAttributeValues"
 * )
 */
class SubmissionValue extends AbstractValue
{
    /**
     * @var Submission
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Bitter\SimpleConfigurator\Entity\Configurator\Submission", inversedBy="attributes")
     * @ORM\JoinColumn(name="submissionId", referencedColumnName="id")
     */
    protected Submission $submission;

    /**
     * @return Submission
     */
    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    /**
     * @param Submission $submission
     * @return SubmissionValue
     */
    public function setSubmission(Submission $submission): SubmissionValue
    {
        $this->submission = $submission;
        return $this;
    }

}
