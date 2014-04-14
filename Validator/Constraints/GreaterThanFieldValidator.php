<?php

namespace CanalTP\MttBundle\Validator\Constraints;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
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
        if ($value <= $this->context->getRoot()->get($constraint->field)->getData()) {
 
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