<?php

namespace CanalTP\MethBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Form\Type\Block\TextType as TextBlockType;
use CanalTP\MethBundle\Form\Handler\Block\TextHandler as TextBlockHandler;
use CanalTP\MethBundle\Form\Type\Block\ImgType as ImgBlockType;
use CanalTP\MethBundle\Form\Handler\Block\ImgHandler as ImgBlockHandler;

class BlockTypeFactory
{
    private $co = null;
    private $om = null;
    private $type = null;
    private $data = null;
    private $instance = null;
    private $formFactory = null;

    public function __construct(Container $co, ObjectManager $om, FormFactoryInterface $formFactory)
    {
        $this->co = $co;
        $this->om = $om;
        $this->formFactory = $formFactory;
    }

    public function init($type, $data, $instance)
    {
        $this->type = $type;
        $this->data = $data;
        $this->instance = $instance;
    }

    private function initForm()
    {
        $objectType = null;

        switch ($this->type) {
            case 'text':
                $objectType = new TextBlockType();
                break;
            case 'img':
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
            case 'text':
                $handler = new TextBlockHandler($this->om, $this->instance);
                break;
            case 'img':
                $handler = new ImgBlockHandler($this->co);
                break;
        }

        return ($handler);
    }
}
