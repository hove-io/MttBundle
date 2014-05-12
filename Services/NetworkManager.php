<?php

/**
 * Description of Network
 *
 * @author rabikhalil
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

class NetworkManager
{
    private $om = null;
    private $repository = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPMttBundle:Network');
    }

    /**
     * Return line Object with navitia data added
     *
     * @param  Integer $lineId
     * @return line
     */
    public function findOneByExternalId($networkId)
    {
        return ($this->repository->findOneByExternalId($networkId));
    }

    /**
     * Return networks Object
     *
     * @return networks
     */
    public function findAll()
    {
        return ($this->repository->findAll());
    }

    /**
     * Return network Object
     *
     * @param  Integer  $lineId
     * @return networks
     */
    public function find($networkId)
    {
        return ($networkId ? $this->repository->find($networkId) : null);
    }

    public function save($network)
    {
        $this->om->persist($network);
        $this->om->flush();
    }

    public function addUserToNetwork($userId, $networkId)
    {
        $this->repository->addUserToNetwork($userId, $networkId);
    }

    public function findUserNetworks($user)
    {
        return $this->repository->findNetworksByUserId($user->getId());
    }

    public function deleteUserNetworks($user)
    {
        return $this->repository->deleteUserNetworks($user->getId());
    }
}
