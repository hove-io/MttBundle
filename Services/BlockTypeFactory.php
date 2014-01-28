<?php

namespace CanalTP\MethBundle\Services;

use Symfony\Component\Form\FormFactoryInterface;
use CanalTP\MethBundle\Form\Type\Block\textType as blockTextType;

class BlockTypeFactory
{
    private $type = null;
    private $data = null;
    private $data_class = null;
    private $formFactory = null;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function init($type, $data, $data_class)
    {
        $this->type = $type;
        $this->data = $data;
        $this->data_class = $data_class;
    }

    public function buildForm()
    {
        $form = null;

        switch ($this->type) {
            case 'text':
                $form = $this->formFactory->createBuilder(new blockTextType(), null, array('data' => $this->data));
                $form->setData($this->data_class);
                break;
        }
        return ($form);
    }

    public function buildHandler()
    {
        $handler = null;

        //TODO: 
        // switch ($this->type) {
        //     case 'text':
        //         $handler = $this->formFactory->get('canal_tp_meth.form.handler.block.text');
        //         break;
        // }
        return ($handler);
    }
}
