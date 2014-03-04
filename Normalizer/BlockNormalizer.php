<?php

namespace CanalTP\MttBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use CanalTP\MttBundle\Entity\Block;

class BlockNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'title'   => $object->getTitle(),
            'content' => $object->getContent(),
        );
    }

    public function denormalize($data, $class, $format = null)
    {
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Block;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return false;
    }
}
