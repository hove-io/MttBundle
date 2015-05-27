<?php
namespace CanalTP\MttBundle\Twig;

class SvgExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'formatSVG'    => new \Twig_Filter_Method($this, 'formatSVG'),
        );
    }

    public function formatSVG($svgContent, $width='100%', $height='100%')
    {
        $result = preg_replace(
            '/(?!<svg.+)(width=.+px" height=.+px")/',
            'width="'.$width.'" height="'.$height.'"',
            $svgContent);

        return $result;
    }

    public function getName()
    {
        return "svg_extension";
    }
}