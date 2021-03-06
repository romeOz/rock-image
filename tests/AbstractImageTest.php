<?php

namespace rockunit;

use rock\base\Alias;
use rock\helpers\FileHelper;
use rock\imagine\Image;

abstract class AbstractImageTest extends \PHPUnit_Framework_TestCase
{
    protected $imageFile;
    protected $watermarkFile;
    protected $runtimeTextFile;
    protected $runtimeWatermarkFile;

    protected function setUp()
    {
        $this->imageFile = Alias::getAlias('@rockunit/data/imagine/large') . '.jpg';
        $this->watermarkFile = Alias::getAlias('@rockunit/data/imagine/xparent') . '.gif';
        $this->runtimeTextFile = Alias::getAlias('@rockunit/runtime/image-text-test') . '.png';
        $this->runtimeWatermarkFile = Alias::getAlias('@rockunit/runtime/image-watermark-test') . '.png';
        FileHelper::createDirectory(Alias::getAlias('@rockunit/runtime/'));
        parent::setUp();
    }

    protected function tearDown()
    {
        @unlink($this->runtimeTextFile);
        @unlink($this->runtimeWatermarkFile);
    }

    public function testText()
    {
        if (!$this->isFontTestSupported()) {
            $this->markTestSkipped('Skipping ImageGdTest Gd not installed');
        }

        $fontFile = Alias::getAlias('@rockunit/data/imagine/GothamRnd-Light') . '.otf';

        $img = Image::text($this->imageFile, 'Rock Image', $fontFile, [0, 0], [
            'size' => 12,
            'color' => '000'
        ]);

        $img->save($this->runtimeTextFile);
        $this->assertTrue(file_exists($this->runtimeTextFile));

    }

    public function testCrop()
    {
        $point = [20, 20];
        $img = Image::crop($this->imageFile, 100, 100, $point);

        $this->assertEquals(100, $img->getSize()->getWidth());
        $this->assertEquals(100, $img->getSize()->getHeight());

    }

    public function testWatermark()
    {
        $img = Image::watermark($this->imageFile, $this->watermarkFile);
        $img->save($this->runtimeWatermarkFile);
        $this->assertTrue(file_exists($this->runtimeWatermarkFile));
    }

    public function testFrame()
    {
        $frameSize = 5;
        $original = Image::getImagine()->open($this->imageFile);
        $originalSize = $original->getSize();
        $img = Image::frame($this->imageFile, $frameSize, '666', 0);
        $size = $img->getSize();

        $this->assertEquals($size->getWidth(), $originalSize->getWidth() + ($frameSize * 2));
    }

    public function testThumbnail()
    {
        $img = Image::thumbnail($this->imageFile, 120, 120);

        $this->assertEquals(120, $img->getSize()->getWidth());
        $this->assertEquals(120, $img->getSize()->getHeight());
    }

    /**
     * @expectedException \rock\imagine\ImagineException
     */
    public function testShouldThrowExceptionOnDriverInvalidArgument()
    {
        Image::setImagine(null);
        Image::$driver = 'fake-driver';
        Image::getImagine();
    }

    abstract protected function isFontTestSupported();
} 