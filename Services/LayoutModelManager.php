<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;

use CanalTP\MttBundle\Entity\Layout;
use CanalTP\MttBundle\Entity\Template;

class LayoutModelManager
{
    private $om;
    private $uploadDir = '/tmp/';
    private $layout;

    public function __construct(ObjectManager $om, $uploadDir)
    {
        $this->om = $om;
        $this->uploadDir = $uploadDir;
        $this->filesystem = new Filesystem();
    }

    /**
     * Persist template and move zip archive's elements.
     *
     * @param Layout $layout
     */
    public function save(Layout $layout)
    {
        $archiveDir = $this->getUploadDir().'/archives';
        $templateDir = $this->getUploadDir().'/templates';
        $tmpDir = $this->getUploadDir().'/tmp/'.time();

        // Copy the archive to the archive directory
        $file = $layout->getFile()->move(
            $archiveDir,
            $layout->getFile()->getClientOriginalName()
        );

        // Extract the archive
        $zip = new \ZipArchive();
        $zip->open($archiveDir.'/'.$layout->getFile()->getClientOriginalName());
        $zip->extractTo($tmpDir);
        $zip->close();

        $id = $this->getUniqueId($layout);
        $config = $this->readConfiguration($tmpDir);

        // Move the assets
        $this->moveFiles(array('*.png', '*.jpg'), $tmpDir, $templateDir.'/img/'.$id);
        $this->moveFiles('*.twig', $tmpDir, $templateDir.'/twig/'.$id);
        $this->moveFiles('*.css', $tmpDir, $templateDir.'/css/'.$id, false);

        // If the layout has a fonts directory, we copy this directory to the css one.
        if ($fontsDirs = $this->getDirectories($tmpDir, 'fonts')) {
            $this->filesystem->remove($templateDir.'/css/'.$id.'/fonts');
            $this->filesystem->rename(current($fontsDirs), $templateDir.'/css/'.$id.'/fonts', true);
        }

        // Remove the tmp directory
        $this->filesystem->remove($tmpDir);

        $this->saveInDb(
            'uploads/'.$id.'/',
            $config['layout']['label'],
            '/bundles/canaltpmtt/img/uploads/'.$id.'/'.$config['layout']['previewFileName'],
            $config['layout']['orientation'],
            $config['templates']
        );
    }

    protected function getUploadDir()
    {
        return $this->uploadDir;
    }

    /**
     * Move files of extension type.
     *
     * @param string|array $extension                 The extensions you need to find
     * @param string       $actualDir                 Source directory
     * @param string       $targetDir                 Target directory
     * @param bool         $throwExceptionIfNotFound Throws an exeption if files are not found
     *
     * @throws Exception If files are not found
     */
    protected function moveFiles($extension, $actualDir, $targetDir, $throwExceptionIfNotFound = true)
    {
        if (!is_array($extension)) {
            $extension = (array) $extension;
        }

        $finder = new Finder();
        $finder->files()->in($actualDir);

        foreach ($extension as $ext) {
            $finder->name($ext);
        }

        if (iterator_count($finder) < 1) {
            if (!$throwExceptionIfNotFound) {
                return;
            }

            throw new \Exception(sprintf('There is no files with extensions %s.', implode(', ', $extension)));
        }

        foreach ($finder as $file) {
            $f = new File($file->getRealpath(), true);
            $f->move($targetDir, $file->getFilename());
        }
    }

    protected function getUniqueId($layout)
    {
        $this->om->persist($layout);
        $this->layout = $layout;

        return $layout->getId();
    }

    protected function saveInDb($path, $label, $previewPath, $orientation, $templates)
    {
        // Do not change the name if we update the layout
        if (null === $this->layout->getLabel()) {
            $this->layout->setLabel($label);
        }
        $this->layout->setPreviewPath($previewPath);
        $this->layout->setOrientation($orientation);
        $this->layout->setNotesModes(array(0 => 1));
        $this->layout->setCssVersion(1);
        $this->layout->setUpdated(new \DateTime());

        // Deleting unused templates
        foreach ($this->layout->getTemplates() as $template)
        {
            if (!array_key_exists($template->getType(), $templates))
            {
                $this->layout->removeTemplate($template);
                $this->om->remove($template);
            }
        }

        // Adding/updating templates
        foreach ($templates as $templateType => $templateConfig)
        {
            if (!in_array($templateType, $this->layout->getTemplatesTypes()))
            {
                $template = new Template();

                $template->setType($templateType);
                $template->setPath($path.$templateConfig['file']);

                $this->om->persist($template);
                $this->layout->addTemplate($template);
            }
            else
            {
                $template = $this->layout->getTemplate($templateType);

                if ($template->getPath() != $path.$templateConfig['file'])
                {
                    $template->setPath($path.$templateConfig['file']);
                    $template->setUpdated(new \Datetime());
                    $this->om->persist($template);
                }
            }
        }

        $this->om->persist($this->layout);
        $this->om->flush();
    }

    /**
     * Return an array from the configuration yml file.
     *
     * @param string $actualDir
     *
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

        $config = Yaml::parse($file->getContents());

        return $config;
    }

    /**
     * Get directories.
     *
     * @param string $dir           Where to search
     * @param string $directoryName The directory name you want to find
     *
     * @return array|bool
     */
    private function getDirectories($dir, $directoryName = null)
    {
        $finder = new Finder();
        $finder->directories()->in($dir);

        if (null !== $directoryName) {
            $finder->name($directoryName);
        }

        if (iterator_count($finder) > 0) {
            return iterator_to_array($finder);
        }

        return false;
    }
}
