<?php
namespace CanalTP\MttBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use CanalTP\MttBundle\Form\Type\BlockType;

class ImgType extends BlockType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('content', 'file',
                array(
                    'data_class' => null,
                    'constraints' => array(
                        new File(
                            array(
                                'mimeTypes' => array(
                                    'image/png',
                                    'image/jpeg'
                                )
                            )
                        )
                    )
                )
            );
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'img_block';
    }
}
