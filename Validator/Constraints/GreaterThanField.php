<?php

namespace CanalTP\MttBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GreaterThanField extends Constraint
{
    public $field = null;

    public function validatedBy()
    {
        return 'validator_constraints_greater_than_field';
    }
}