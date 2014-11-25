<?php
namespace CanalTP\MttBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use CanalTP\MttBundle\Form\Type\BlockType;

class ImgType extends BlockType
{
    const MIME_IMAGETYPE_PNG = 'image/png';
    // used in ImgHandler to determine if conversion is needed
    const MIME_IMAGETYPE_JPEG = 'image/jpeg';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                    'label' => 'block.img.labels.title',
                    'required' => false
                )
            )
            ->add('content', 'file',
                array(
                    'data_class' => null,
                    'constraints' => array(
                        new File(
                            array(
                                'mimeTypes' => array(
                                    self::MIME_IMAGETYPE_PNG,
                                    self::MIME_IMAGETYPE_JPEG
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
