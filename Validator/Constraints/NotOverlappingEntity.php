<?php

namespace CanalTP\MttBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotOverlappingEntity extends Constraint
{
    public $parent = null;
    public $siblings = null;
    public $startField = null;
    public $endField = null;
    public $message = null;

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'not_overlapping_entity';
    }
}
