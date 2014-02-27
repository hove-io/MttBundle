<?php

/**
 * UserManager service for user related needs
 *
 * @author vdegroote
 */
namespace CanalTP\MethBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class UserManager
{
    private $om = null;
    private $sc = null;
    private $translator = null;
    
    public function __construct(
        ObjectManager $om,
        SecurityContext $sc,
        Translator $translator
    )
    {
        $this->om = $om;
        $this->sc = $sc;
        $this->translator = $translator;
    }
    
    public function getNetworks()
    {
        $networks = $this->om
            ->getRepository('CanalTPMethBundle:Network')
            ->findNetworksByUserId($this->sc->getToken()->getUser()->getId());

        if (count($networks) == 0) {
            throw new \Exception(
                $this->translator->trans(
                    'controller.default.navigation.no_networks', 
                    array(), 
                    'exceptions'
                )
            );
        }

        return $networks;
    }
}