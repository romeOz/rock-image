<?php

namespace rock\image;

use Imagine\Image\ImageInterface;
use rock\base\Alias;
use rock\base\BaseException;
use rock\base\ObjectInterface;
use rock\base\ObjectTrait;
use rock\file\FileManager;
use rock\helpers\Instance;
use rock\imagine\Image;
use rock\log\Log;

class ImageProvider implements ObjectInterface
{
    use ObjectTrait;

    public $width;
    public $height;
    public $maxFiles = 100;
    public $srcImage = '@web/images';
    public $srcCache = '@web/cache';
    /** @var  callable */
    public $handler;
    /** @var FileManager|array */
    public $adapter ;
    /** @var FileManager|array */
    public $adapterCache;
    protected $resource;
    /** @var  string */
    protected $src;
    /** @var  ImageInterface */
    protected $image;

    public function init()
    {
        if (!$this->adapter = Instance::ensure($this->adapter, null, false)) {
            throw new ImageException(ImageException::NOT_INSTALL_FILE);
        }

        $this->adapterCache = Instance::ensure($this->adapterCache);

        $this->srcImage = Alias::getAlias($this->srcImage);
        $this->srcCache = Alias::getAlias($this->srcCache);
    }

    public function get($path, $width = 0, $height = 0)
    {
        $path = $this->preparePath($path);
        if (!$this->adapter->has($path)) {
            return $this->srcImage . '/'. ltrim($path, '/');
        }
        $this->resource = $this->adapter->readStream($path);

        if ((empty($width) && empty($height)) || empty($this->adapterCache)) {
            return $this->srcImage . '/'. ltrim($path, '/');
        }
        $this->image = Image::getImagine()->read($this->resource);
        $this->calculateDimensions($width, $height);
        $this->prepareImage($path);

        return $this->src;
    }

    protected function preparePath($path)
    {
        if (empty($path)) {
            return '';
        }

        return str_replace($this->srcImage , '', $path);
    }

    protected function calculateDimensions($width = null, $height = null)
    {
        if (empty($width)) {
            $width = $this->image->getSize()->getWidth();
        }
        if (empty($height)) {
            $height = $this->image->getSize()->getHeight();
        }

        $this->width = $width;
        $this->height = $height;
    }

    protected function prepareImage($path)
    {
        $metadata = $this->adapter->getMetadata($path);
        $path = implode(DIRECTORY_SEPARATOR, [trim($metadata['dirname'], DIRECTORY_SEPARATOR), "{$this->width}x{$this->height}", $metadata['basename']]);
        $this->src = $this->srcCache . '/'. ltrim($path, '/');
        if ($this->adapterCache->has($path)) {
            return;
        }
        if ($this->handler instanceof \Closure) {
            call_user_func($this->handler, $path, $this);
            return;
        }
        $string = Image::thumbnail($this->image, $this->width, $this->height)->get('jpg');

        if (!$this->adapterCache->write($path, $string)) {
            if (class_exists('\rock\log\Log')) {
                $message = BaseException::convertExceptionToString(new ImageException(ImageException::NOT_CREATE_FILE, ['path' => $path]));
                Log::warn($message);
            }
        }
    }
}