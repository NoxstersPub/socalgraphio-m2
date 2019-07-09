<?php

namespace Blackbox\Epace\Helper\Event;

class File extends \Magento\Framework\App\Helper\AbstractHelper 
{
    public function getFullPath($path)
    {
        return $this->getDir() . DS . $path;
    }

    public function createFileName($ext)
    {
        return substr( base_convert( time(), 10, 36 ) . md5( microtime() ), 0, 16 ) . '.' . $ext;
    }

    public function getDir()
    {
        return Mage::getBaseDir('var') . DS . 'Epace';
    }

    public function writeFile($path, $content)
    {
        $path = $this->getFullPath($path);
        $dir = pathinfo($path)['dirname'];

        $file = new Varien_Io_File();
        if (!file_exists($dir)) {
            $file->mkdir($dir, 0777, true);
        }

        $file->write($path, $content);
    }

    public function deleteFile($path)
    {
        $file = new Varien_Io_File();
        $file->rm($this->getFullPath($path));
    }
}