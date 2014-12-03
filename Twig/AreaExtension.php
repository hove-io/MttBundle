<?php

namespace CanalTP\MttBundle\Twig;

class AreaExtension extends \Twig_Extension
{
    private $om;
    private $translator;
    private $areaPdfManager;
    private $areaPdfRepository;

    public function __construct($om, $translator, $areaPdfManager)
    {
        $this->om = $om;
        $this->translator = $translator;
        $this->areaPdfManager = $areaPdfManager;
        $this->areaPdfRepository = $om->getRepository('CanalTPMttBundle:AreaPdf');
    }

    public function getFilters()
    {
        return array(
            'hasPdf' => new \Twig_Filter_Method($this, 'hasPdf')
        );
    }

    public function hasPdf($area, $season)
    {
        $areaPdf = $this->areaPdfRepository->findOneBy(
            array(
                'area' => $area->getId(),
                'season' => $season->getId(),
            )
        );

        return ($areaPdf && $areaPdf->getGeneratedAt() ? $this->areaPdfManager->findPdfPath($areaPdf) : null);
    }

    public function getName()
    {
        return 'area_extension';
    }
}
