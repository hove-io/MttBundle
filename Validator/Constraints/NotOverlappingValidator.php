<?php

namespace CanalTP\MttBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

use CanalTP\MttBundle\Twig\CalendarExtension;

/**
 * GreaterThanFieldValidator
 */
class NotOverlappingValidator extends ConstraintValidator
{
    private $calendarExt = null;
    private $values = null;

    public function __construct()
    {
        $this->calendarExt = new CalendarExtension();
    }

    private function getIndex($value)
    {
        return  $this->calendarExt->hourIndex($value, $this->values);
    }

    private function idxIsInOtherEntity($idx, $otherEntityStartIdx, $otherEntityEndIdx)
    {
        return $idx >= $otherEntityStartIdx && $idx <= $otherEntityEndIdx;
    }

    private function entityIsIncludedInAnother($startIdx, $endIdx, $otherEntityStartIdx, $otherEntityEndIdx)
    {
        return $startIdx >= $otherEntityStartIdx && $endIdx <= $otherEntityEndIdx;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constrain for the validation
     *
     * @return Boolean Whether or not the value is valid
     */
    public function validate($value, Constraint $constraint)
    {
        $this->values = $constraint->values;
        $startMethod = 'get' . $constraint->startField;
        $endMethod = 'get' . $constraint->endField;
        for ($i = 0; $i < count($value); $i++) {
            $entity = $value[$i];

            $startIdx = $this->getIndex($entity->$startMethod());
            $endIdx = $this->getIndex($entity->$endMethod());

            if ($startIdx >= $endIdx) {
                $this->context->addViolation('error.start_end_time_incoherent');

                return false;
            }
            for ($j = $i + 1; $j < count($value); $j++) {
                $entityToCheck = $value[$j];
                $startIdxToCheck = $this->getIndex($entityToCheck->$startMethod());
                $endIdxToCheck = $this->getIndex($entityToCheck->$endMethod());
                if ($this->idxIsInOtherEntity($startIdx, $startIdxToCheck, $endIdxToCheck) ||
                    $this->idxIsInOtherEntity($endIdx, $startIdxToCheck, $endIdxToCheck) ||
                    $this->entityIsIncludedInAnother($startIdx, $endIdx, $startIdxToCheck, $endIdxToCheck) ||
                    $this->entityIsIncludedInAnother($startIdxToCheck, $endIdxToCheck, $startIdx, $endIdx)
                ) {
                    $this->context->addViolation('error.entities_overlapping', array(
                        '%firstElement%' => $i + 1,
                        '%secondElement%' => $j + 1,
                    ));

                    return false;
                }
            }
        }

        return true;
    }
}
