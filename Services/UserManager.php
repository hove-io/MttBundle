<?php

/**
 * UserManager service for user related needs
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class UserManager
{
    private $om = null;
    private $translator = null;

    public function __construct(
        ObjectManager $om,
        Translator $translator
    )
    {
        $this->om = $om;
        $this->translator = $translator;
    }

    /**
     * Retrieve current user networks
     */
    public function getNetworks($user)
    {

        $networks = $this->om
            ->getRepository('CanalTPMttBundle:Network')
            ->findNetworksByUserId($user->getId());

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
