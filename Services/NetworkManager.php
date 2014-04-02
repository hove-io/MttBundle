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
}
