<?php

namespace CanalTP\MttBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * GreaterThanFieldValidator
 */
class NotOverlappingEntityValidator extends ConstraintValidator
{
    private $startFieldGetter = null;
    private $endFieldGetter = null;
    private $seasonManager = null;

    public function __construct($seasonManager)
    {
        $this->seasonManager = $seasonManager;
    }

    private function entityIsIncludedInAnother($entity, $otherEntity)
    {
        return  $entity->{$this->startFieldGetter}() >= $otherEntity->{$this->startFieldGetter}() &&
                $entity->{$this->endFieldGetter}() <= $otherEntity->{$this->endFieldGetter}();
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
        $parentGetter = 'get' . ucfirst($constraint->parent);
        $siblingsGetter = 'get' . ucfirst($constraint->siblings);
        $this->startFieldGetter = 'get' . $constraint->startField;
        $this->endFieldGetter = 'get' . $constraint->endField;
        $entities = $this->seasonManager->findByPerimeter($value->$parentGetter());

        foreach ($entities as $entity) {
            if (
                $entity->getId() != $value->getId() && (
                // value is contained in entity
                $this->entityIsIncludedInAnother($entity, $value) ||
                // entity is contained in value
                $this->entityIsIncludedInAnother($value, $entity) ||
                // value startDate is within another entity
                ($value->{$this->startFieldGetter}() >= $entity->{$this->startFieldGetter}() &&
                $value->{$this->startFieldGetter}() <= $entity->{$this->endFieldGetter}()) ||
                // value endDate is within another entity
                ($value->{$this->endFieldGetter}() >= $entity->{$this->startFieldGetter}() &&
                $value->{$this->endFieldGetter}() <= $entity->{$this->endFieldGetter}())
            )) {
                $this->context->addViolation(
                    'error.seasons_overlapping',
                    array(
                        '%saison_title%' => $entity->getTitle(),
                    )
                );

                return false;
            }
        }

        return true;
    }
}
