<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BlockRepository
 *
 */
class BlockRepository extends EntityRepository
{
    const TEXT_TYPE      = 'text';
    const IMG_TYPE       = 'img';
    const CALENDAR_TYPE  = 'calendar';
}
