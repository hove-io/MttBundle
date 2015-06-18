<?php

namespace CanalTP\MttBundle\Twig;

class StopPointExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('code', array($this, 'getCode')),
        );
    }

    /**
     * Get the code by type
     * If type = external_code we strip the 3 first characters
     *
     * @param array  $codes Array of codes
     * @param string $type  the type (external_code, totem...)
     *
     * @return string|null The code
     */
    public function getCode($codes, $type)
    {
        foreach ($codes as $code) {
            if ($code->type === $type) {
                return $code->type === 'external_code' ? substr($code->value, 3) : $code->value;
            }
        }

        return;
    }

    public function getName()
    {
        return 'stop_point_extension';
    }
}
