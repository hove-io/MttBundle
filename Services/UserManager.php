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
    private $container = null;

    public function __construct(
        ObjectManager $om,
        Translator $translator,
        $container
    )
    {
        $this->om = $om;
        $this->translator = $translator;
        $this->container = $container;
    }

    /**
     * Retrieve current user networks
     */
    public function getNetworks($user = null)
    {
        if ($user == null) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }
        if ($user === 'anon.') {
            return (array());
        }
        $networks = $this->om
            ->getRepository('CanalTPMttBundle:Network')
            ->findNetworksByUserId(
                $user->getId()
            );

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
