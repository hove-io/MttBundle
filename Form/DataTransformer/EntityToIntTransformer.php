<?php

namespace CanalTP\MttBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class EntityToIntTransformer implements DataTransformerInterface
{
    private $om;
    private $class;

    /**
     * @param ObjectManager $om
     * @param string $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->om = $om;
        $this->class = $class;
    }

    /**
     * @param mixed $entity
     *
     * @return integer
     */
    public function transform($entity)
    {
        if (null === $entity || !($entity instanceof $this->class)) {
            return null;
        }

        return $entity->getId();
    }

    /**
     * @param mixed $id
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @return mixed|object
     */
    public function reverseTransform($identifier)
    {
        if (!$identifier) {
            return null;
        }

        $entity = $this->om->getRepository($this->class)->find($identifier);

        if ($entity === null) {
            throw new TransformationFailedException(sprintf(
                '%s with id %s not found',
                $this->class,
                $identifier
            ));
        }

        return $entity;
    }
}
