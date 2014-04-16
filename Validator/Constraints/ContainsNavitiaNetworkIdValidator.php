<?php

namespace CanalTP\MttBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class ContainsNavitiaNetworkIdValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^network:[a-zA-Z0-9]+$/', $value, $matches)) {
            $this->context->addViolation('error.navitia_network_id', array('%string%' => $value));
        }
    }
}
