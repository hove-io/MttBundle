<?php

namespace CanalTP\MethBundle\Services;

use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Form\Type\Block\TextType as textBlockType;
use CanalTP\MethBundle\Form\Handler\Block\textHandler as textBlockHandler;
use CanalTP\MethBundle\Form\Type\Block\ImgType as imgBlockType;

class BlockTypeFactory
{
    private $om = null;
    private $type = null;
    private $data = null;
    private $instance = null;
    private $formFactory = null;

    public function __construct(FormFactoryInterface $formFactory, ObjectManager $om)
    {
        $this->om = $om;
        $this->formFactory = $formFactory;
    }

    public function init($type, $data, $instance)
    {
        $this->type = $type;
        $this->data = $data;
        $this->instance = $instance;
    }

    public function buildForm()
    {
        $form = null;

        switch ($this->type) {
            case 'text':
                $form = $this->formFactory->createBuilder(
                    new textBlockType(),
                    null,
                    array('data' => $this->data)
                );
                $form->setData($this->instance);
                break;
            case 'img':
                $form = $this->formFactory->createBuilder(
                    new imgBlockType(),
                    null,
                    array('data' => $this->data)
                );
                $form->setData($this->instance);
                break;
        }

        return ($form);
    }

    public function buildHandler()
    {
        $handler = null;

        switch ($this->type) {
            case 'text':
                $handler = new textBlockHandler($this->om, $this->instance);
                break;
        }

        return ($handler);
    }
}
