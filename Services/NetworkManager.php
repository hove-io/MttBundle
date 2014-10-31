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
    private $perimeterManager = null;

    public function __construct(ObjectManager $om, $perimeterManager)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPNmmPortalBundle:Perimeter');
        $this->perimeterManager = $perimeterManager;
    }

    /**
     * Return line Object with navitia data added
     *
     * @param  Integer $lineId
     * @return line
     */
    public function findOneByExternalId($externalNetworkId)
    {
        return $this->perimeterManager->findOneByExternalNetworkId($externalNetworkId);
    }

    /**
     * @alias findOneByExternalId($externalNetworkId)
     * @param type $externalNetworkId
     * @return type
     */
    public function getByExternalNetworkId($externalNetworkId)
    {
        return $this->findOneByExternalId($externalNetworkId);
    }

    public function getSeasons($externalNetworkId)
    {
        $perimeter = $this->findOneByExternalId($externalNetworkId);
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
    public function getLastTasks($perimeter, $limit = 10)
    {
        $taskRepo = $this->om->getRepository('CanalTPMttBundle:AmqpTask');

        return $taskRepo->getLastPerimeterTasks($perimeter, $limit);
    }

    /**
     * Return network Object
     *
     * @param  Integer  $lineId
     * @return networks
     */
    public function find($networkId)
    {
        return ($networkId ? $this->perimeterManager->find($networkId) : null);
    }

    public function save($network, $networkId)
    {
        $entityNetwork = $this->find($networkId);
        // form is modified dinamycally so we need to refresh entity (id is lost during process)
        if (!empty($entityNetwork)) {
            $entityNetwork->setExternalId($network->getExternalNetworkId());
            $entityNetwork->setExternalCoverageId($network->getExternalCoverageId());
            $entityNetwork->setToken($network->getToken());
            $entityNetwork->setLayoutConfigs($network->getLayoutConfigs());
        } else {
            $entityNetwork = $network;
            $this->om->persist($entityNetwork);
        }
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
