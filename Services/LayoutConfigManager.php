<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\LayoutConfig;
use Symfony\Component\Security\Core\SecurityContext;

class LayoutConfigManager
{
    private $om = null;
    private $currentCustomer = null;
    private $repository = null;

    public function __construct(ObjectManager $om, SecurityContext $securityContext)
    {
        $this->currentCustomer = $securityContext->getToken()->getUser()->getCustomer();
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:LayoutConfig');
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    public function find($layoutConfigId)
    {
        return empty($layoutConfigId) ? null : $this->repository->find($layoutConfigId);
    }

    public function save($layoutConfig)
    {
        $this->om->persist($layoutConfig);
        $layoutConfig->upload();
        //TODO: Add NotesMode field in LayoutConfigType. (Create custom Layout)
        $layoutConfig->setNotesMode(LayoutConfig::NOTES_MODE_DISPATCHED);
        $this->om->flush();
    }

    public function findLayoutConfigByCustomer()
    {
        return ($this->repository->findLayoutConfigByCustomer($this->currentCustomer));
    }

    public function delete($layoutConfig)
    {
        foreach ($layoutConfig->getLineConfigs() as $lineConfig) {
            $lineConfig->setLayoutConfig(null);
        }
        $this->om->remove($layoutConfig);
        $this->om->flush();
    }
}
