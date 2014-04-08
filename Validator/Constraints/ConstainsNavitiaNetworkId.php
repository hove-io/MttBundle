<?php

namespace CanalTP\MttBundle\Validator\Constraints;

// use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ConstainsNavitiaNetworkId extends Constraint
{
    public $message = null;

    public function validatedBy()
    {
        return 'validator_constraints_navitia_network_id';
    }
}