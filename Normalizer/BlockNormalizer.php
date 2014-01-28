<?php

namespace CanalTP\MethBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use CanalTP\MethBundle\Entity\Block;

class BlockNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    function normalize($object, $format = null, array $context = array())
    {
        return array(
            'title'   => $object->getTitle(), 
            'content' => $object->getContent(), 
        );
    }

    function denormalize($data, $class, $format = null) {}

    function supportsNormalization($data, $format = null)
    {
        return $data instanceof Block;
    }

    function supportsDenormalization($data, $type, $format = null) 
    {
        return false;
    }
}