<?php

namespace CanalTP\MttBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\Serializer;

use CanalTP\MttBundle\Normalizer\BlockNormalizer;
use CanalTP\MttBundle\Entity\BlockRepository;
// Text Block
use CanalTP\MttBundle\Form\Type\Block\TextType as TextBlockType;
use CanalTP\MttBundle\Form\Handler\Block\TextHandler as TextBlockHandler;
// Image Block
use CanalTP\MttBundle\Form\Type\Block\ImgType as ImgBlockType;
use CanalTP\MttBundle\Form\Handler\Block\ImgHandler as ImgBlockHandler;
// Calendar Block
use CanalTP\MttBundle\Form\Type\Block\CalendarType as CalendarBlockType;
use CanalTP\MttBundle\Entity\Block;

class BlockTypeFactory
{
    private $co = null;
    private $om = null;
    private $mediaManager = null;
    private $formFactory = null;

    private $type = null;
    private $data = null;
    private $externalCoverageId = null;
    private $oldData = array();
    private $instance = null;
    private $navitia = null;

    public function __construct(
        Container $co,
        ObjectManager $om,
        FormFactoryInterface $formFactory,
        MediaManager $mediaManager,
        Navitia $navitia
    )
    {
        $this->co = $co;
        $this->om = $om;
        $this->mediaManager = $mediaManager;
        $this->formFactory = $formFactory;
        $this->navitia = $navitia;
    }

    /**
     * Initializing a block with data
     *
     * @param string $type
     * @param array $data
     * @param Block $block
     * @param string $externalCoverageId
     */
    public function init($type, $data, Block $block = null, $externalCoverageId)
    {
        $this->type = $type;
        $this->data = $data;
        $this->instance = $block;
        $this->externalCoverageId = $externalCoverageId;
        $serializer = new Serializer(array(new BlockNormalizer()));
        // store data before we give Entity to forms (used by ImgBlock so far)
        $this->oldData = $serializer->normalize($this->instance);
    }

    private function initForm()
    {
        $objectType = null;

        switch ($this->type) {
            case BlockRepository::CALENDAR_TYPE:
                $objectType = new CalendarBlockType(
                    $this->co->get('canal_tp_mtt.calendar_manager'),
                    $this->instance,
                    $this->externalCoverageId,
                    $this->navitia
                );
                break;
            case BlockRepository::TEXT_TYPE:
                $objectType = new TextBlockType();
                break;
            case BlockRepository::IMG_TYPE:
                $objectType = new ImgBlockType();
                break;
        }

        return ($objectType);
    }

    public function buildForm()
    {
        $form = $this->formFactory->createBuilder(
            $this->initForm(),
            null,
            array('data' => $this->data)
        );

        $form->setData($this->instance);

        return ($form);
    }

    public function buildHandler()
    {
        $handler = null;

        switch ($this->type) {
            case BlockRepository::CALENDAR_TYPE:
            case BlockRepository::TEXT_TYPE:
                $handler = new TextBlockHandler($this->om, $this->instance);
                break;
            case BlockRepository::IMG_TYPE:
                $handler = new ImgBlockHandler(
                    $this->co,
                    $this->om,
                    $this->mediaManager,
                    $this->instance,
                    $this->oldData['content']
                );
                break;
        }

        return ($handler);
    }
}
