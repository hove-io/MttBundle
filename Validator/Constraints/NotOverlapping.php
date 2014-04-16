<?php

namespace CanalTP\MttBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotOverlapping extends Constraint
{
    public $startField = null;
    public $endField = null;
    public $values = null;
}
