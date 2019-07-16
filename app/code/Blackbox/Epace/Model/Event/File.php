<?php

namespace Blackbox\Epace\Model\Event;

use Magento\Store\Model\ScopeInterface;

class File extends \Magento\Framework\Model\AbstractModel
{
    protected $fullpath = null;

    protected function _construct() {
        $this->_init('Blackbox\Epace\Model\Event\File');
    }

    public function getName() {
        return pathinfo($this->getPath())['basename'];
    }

    public function getDownloadName()
    {
        $ext = pathinfo($this->getPath())['extension'];
        if ($ext) {
            $ext = '.' . $ext;
        }
        switch ($this->getType()) {
            case 'statistic':
                return $this->getAction() . $ext;
            case 'response':
            case 'request':
                return $this->getAction() . ' ' . $this->getType() . $ext;
            default:
                return $this->getAction() . $ext;
        }
    }

    public function getDate() {
        return date ("F d Y H:i:s.", filemtime($this->getFullPath()));
    }

    public function getFullPath() {
        if (!$this->fullpath) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $fileHelper = $objectManager->create('\Blackbox\Epace\Helper\Event\File');
            $this->fullpath =  $fileHelper->getFullPath($this->getPath());
        }
        return $this->fullpath;
    }

    public function exists()
    {
        return file_exists($this->getFullPath());
    }

    public function save()
    {
        if (isset($this->_data['content'])) {
            $path = $this->getPath();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $fileHelper = $objectManager->create('\Blackbox\Epace\Helper\Event\File');
            if ($path) {
                if (substr($path, strlen($path) - 1, 1) == '/') {
                    $path .= $fileHelper->createFileName($this->getExt() ? $this->getExt() : 'txt');
                    $this->setPath($path);
                }
            } else {
                $path = $fileHelper->createFileName($this->getExt() ? $this->getExt() : 'txt');
                $this->setPath($path);
            }

            $fileHelper->writeFile($path, $this->_data['content']);
        }
        return parent::save();
    }

    public function delete()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileHelper = $objectManager->create('\Blackbox\Epace\Helper\Event\File');
        $fileHelper->deleteFile($this->getPath());
        return parent::delete();
    }
}
