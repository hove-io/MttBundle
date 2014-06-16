<?php
namespace CanalTP\MttBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use CanalTP\MttBundle\Form\Type\BlockType;

class ImgType extends BlockType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('label' => 'block.img.labels.title'))
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
                        ),
                        new NotBlank()
                    ),
                    'label' => 'block.img.labels.content'
                )
            );
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'img_block';
    }
}
