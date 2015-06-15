<?php

namespace CanalTP\MttBundle\Twig;

use CanalTP\MttBundle\Entity\Layout;
use Symfony\Component\Translation\TranslatorInterface;

class LayoutExtension extends \Twig_Extension
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('orientationName', array($this, 'getOrientationName')),
        );
    }

    public function getOrientationName($orientationType)
    {
        switch ($orientationType) {
            case Layout::ORIENTATION_LANDSCAPE:
                $name =  'landscape';
                break;
            case Layout::ORIENTATION_PORTRAIT:
                $name =  'portrait';
                break;
            default:
                $name = 'unknown';
                break;
        }

        return $this->translator->trans('layout.orientation.'.$name);
    }

    public function getName()
    {
        return 'layout_extension';
    }
}
