<?php

namespace CanalTP\MttBundle\MediaManager\Strategy;

use CanalTP\MediaManager\Company\CompanyInterface;
use CanalTP\MediaManager\Category\CategoryInterface;
use CanalTP\MediaManager\Strategy\AbstractStrategy;

class DefaultStrategy extends AbstractStrategy
{
    private function buildPath($path, $category)
    {
        if ($category->getParent()) {
            $path .= $this->buildPath($path, $category->getParent());
        }
        $path .= $category->getRessourceId() . '/' . $category->getId() . '/';

        return ($path);
    }

    public function generatePath($media)
    {
        $category = $media->getCategory();
        $path = $media->getCompany()->getName() . '/';

        $path .= $this->buildPath("", $category);
        $path .= $media->getBaseName();

        return ($path);
    }

    public function generateCategoryPath(
        CompanyInterface $company,
        CategoryInterface $category
    )
    {
        $path = $company->getStorage()->getPath();
        $path .= $company->getName() . '/';

        $path .= $this->buildPath("", $category);

        return ($path);
    }

    public function generateRelativeCategoryPath(
        CompanyInterface $company,
        CategoryInterface $category
    )
    {
        $path = $company->getName() . '/';

        $path .= $this->buildPath("", $category);

        return ($path);
    }

    public function getMediasPathByCategory(
        CompanyInterface $company,
        CategoryInterface $category
    )
    {
        $path = $company->getStorage()->getPath();
        $path .= $category->getName() . '/';
        $path .= $category->getRessourceId();

        if (!file_exists($path)) {
            return (array());
        }

        $files = array_diff(scandir($path), array('..', '.'));
        $medias = array();

        foreach ($files as $file) {
            $mediaPath = $path . '/' . $file;

            if (!is_dir($mediaPath)) {
                array_push($medias, $mediaPath);
            }
        }

        return ($medias);
    }

    public function findMedia(
        CompanyInterface $company,
        CategoryInterface $category,
        $mediaId
    )
    {
        $path = $company->getStorage()->getPath();
        $path .= $company->getName() . '/';
        $path .= $this->buildPath("", $category);

        if (!file_exists($path)) {
            return;
        }

        $files = array_diff(scandir($path), array('..', '.'));

        foreach ($files as $file) {
            $mediaPath = $path . $file;
            $mediaId = pathinfo($mediaId, PATHINFO_FILENAME);

            if (is_dir($mediaPath)) {
                continue;
            }
            if (pathinfo($file, PATHINFO_FILENAME) == $mediaId) {
                return ($mediaPath);
            }
        }

        return (null);
    }
}
