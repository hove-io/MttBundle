<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\LayoutConfig;
use Symfony\Component\Finder\Finder;

class LayoutModelManager
{
    protected $om = null;
    protected $repository = null;
    protected $uploadDir = '/tmp/';
    private $layout;

    public function __construct(ObjectManager $om, $uploadDir)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:Layout');
        $this->uploadDir = $uploadDir;
    }

    /**
     * Persist template and move zip archive's elements
     * @param type $model
     */
    public function save($model)
    {
        $archiveDir = $this->getUploadDir() . '/archives';
        $templateDir = $this->getUploadDir() . '/templates';
        $tmpDir = $this->getUploadDir() . '/tmp/' . time();

        $file = $model->getFile()->move(
            $archiveDir,
            $model->getFile()->getClientOriginalName()
        );

        $zip = new \ZipArchive();
        $zip->open($archiveDir . '/' . $model->getFile()->getClientOriginalName());
        $zip->extractTo($tmpDir);
        $zip->close();

        $id = $this->getUniqueId();
        $config = $this->readConfiguration($tmpDir);
        $this->movePictures($tmpDir, $templateDir.'/img/' . $id);
        $this->moveTwigFiles($tmpDir, $templateDir, $id);
        $this->moveCssFiles($tmpDir, $templateDir, $id);

        $this->saveInDb($config['label'], 'uploads/' . $id . '/' . $config['templateName'], '/bundles/canaltpmtt/img/uploads/' . $id . '/' . $config['previewFileName'], $config['orientation']);
    }

    protected function getUploadDir()
    {
        return $this->uploadDir;
    }

    /**
     * Move from working directory to final directory the first image found
     *
     * @param String $actualDir working directory
     * @param String $targetDir destination directory
     * @return String FileName
     */
    protected function movePictures($actualDir, $targetDir)
    {
        $finder = new Finder();
        $finder->files()->in($actualDir)->name('*.png')->name('*.jpg');

//        $files = iterator_to_array($finder);
        if (iterator_count($finder) < 1) {
            throw new \Exception('The preview file is missing.');
        }
//        $file = current($files);

        foreach ($finder as $file) {
            $f = new \Symfony\Component\HttpFoundation\File\File($file->getRealpath(), true);

            $f->move(
                $targetDir,
                $file->getFilename()
            );
        }
    }

    /**
     * Move files of extension type
     *
     * @param String $extension exemple: '*.EXTENSION'
     * @param type $actualDir
     * @param type $targetDir
     */
    protected function moveFiles($extension, $actualDir, $targetDir)
    {
        $finder = new Finder();
        $finder->files()->in($actualDir)->name($extension);

        if (iterator_count($finder) < 1) {
            throw new \Exception('There\'s no "' . $extension . '" file.');
        }

        foreach ($finder as $file) {
            $f = new \Symfony\Component\HttpFoundation\File\File($file->getRealpath(), true);

            $f->move(
                $targetDir,
                $file->getFilename()
            );
        }
    }

    protected function moveCssFiles($actualDir, $targetDir, $id)
    {
        try {
            $this->moveFiles('*.css', $actualDir, $targetDir . '/css/' . $id);
        } catch(\Exception $e) {
            return false;
        }
    }

    protected function moveTwigFiles($actualDir, $targetDir, $id)
    {
        $this->moveFiles('*.twig', $actualDir, $targetDir . '/twig/' . $id);
    }

    protected function getUniqueId()
    {
        $layout = new \CanalTP\MttBundle\Entity\Layout();
        $this->om->persist($layout);
        $this->layout = $layout;

        return $layout->getId();
    }

    protected function saveInDb($label, $twigPath, $previewPath, $orientation)
    {
        $this->layout->setLabel($label);
        $this->layout->setPath($twigPath);
        $this->layout->setPreviewPath($previewPath);
        $this->layout->setOrientation($orientation);
        $this->layout->setNotesModes(array(0 => 1));
        $this->layout->setCssVersion(1);

        $this->om->flush($this->layout);
    }

    /**
     * Return an array from the configuration yml file
     *
     * @param string $actualDir
     * @return array
     */
    protected function readConfiguration($actualDir)
    {
        $finder = new Finder();
        $finder->files()->in($actualDir)->name('*.yml');

        if (iterator_count($finder) < 1) {
            throw new \Exception('The configuration (.yml) file is missing.');
        }

        $files = iterator_to_array($finder);
        $file = current($files);

        $config = \Symfony\Component\Yaml\Yaml::parse($file->getContents());

        return $config;
    }
}
