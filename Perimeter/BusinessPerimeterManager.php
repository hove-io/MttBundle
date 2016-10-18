<?php

namespace CanalTP\MttBundle\Perimeter;

use FOS\UserBundle\Model\UserInterface;
use CanalTP\MttBundle\Services\PerimeterManager;
use CanalTP\SamEcoreApplicationManagerBundle\Perimeter\AbstractBusinessPerimeterManager;
use CanalTP\SamEcoreApplicationManagerBundle\Perimeter\BusinessPerimeterInterface;
use CanalTP\SamEcoreApplicationManagerBundle\Perimeter\BusinessPerimeter;

class BusinessPerimeterManager extends AbstractBusinessPerimeterManager
{
    private $perimeters;

    public function __construct(PerimeterManager $perimeterManager)
    {
        $this->perimeterManager = $perimeterManager;
    }

    /**
     * Add a user to a perimeter
     *
     * @param UserInterface              $user
     * @param BusinessPerimeterInterface $perimeter
     */
    public function addUserToPerimeter(UserInterface $user, BusinessPerimeterInterface $perimeter)
    {
        $this->perimeterManager->addUserToPerimeter($user->getId(), $perimeter->getId());
    }

    /**
     * Get the perimeters
     *
     * @return BusinessPerimeterInterface[] The perimeters
     * @DeadCode, it seems
     */
    public function getPerimeters()
    {
        if (null === $this->perimeters) {
            $perimeters = array();
            foreach ($this->perimeterManager->findAll() as $network) {
                $perimeter = new BusinessPerimeter($network->getExternalPerimeterId());
                $perimeter->setId($network->getId());
                $this->perimeters[] = $perimeter;
            }
        }

        return $this->perimeters;
    }

    /**
     * Delete a user from a perimeter
     *
     * @param UserInterface $user
     */
    public function deleteUserPerimeters(UserInterface $user)
    {
        // @todo Implement the functionality
    }

    /**
     * Get a user's perimeters
     *
     * @param UserInterface $user
     *
     * @return BusinessPerimeterInterface[] The perimeters
     */
    public function getUserPerimeters(UserInterface $user)
    {
        $userPerimeters = array();
        foreach ($this->perimeterManager->findUserPerimeters($user) as $network) {
            foreach ($this->getPerimeters() as $perimeter) {
                if ($perimeter->getId() == $network['id'] && $perimeter->getName() == $network['external_id']) {
                    $userPerimeters[] = $perimeter;
                    break 1;
                }
            }
        }

        return $userPerimeters;
    }
}
