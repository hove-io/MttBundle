<?php

namespace CanalTP\MttBundle\Validator\Constraints;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class ContainsNavitiaNetworkIdValidator extends ConstraintValidator
{
    private $message = null;

    public function __construct(Translator $translator)
    {
        $this->message = $translator->trans(
            'error.navitia_network_id',
            array(),
            'messages'
        );
    }

    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^network:[a-zA-Z0-9]+$/', $value, $matches)) {
            $this->context->addViolation($this->message, array('%string%' => $value));
        }
    }
}