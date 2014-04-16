<?php

namespace CanalTP\MttBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * GreaterThanFieldValidator
 */
class GreaterThanFieldValidator extends ConstraintValidator
{
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
        $field = $this->context->getRoot()->get($constraint->field);

        if ($value <= $field->getData()) {

            $this->context->addViolation(
                'error.greater_than_field',
                array(
                    '%field%' => $constraint->field,
                )
            );

            return false;
        }

        return true;
    }
}
