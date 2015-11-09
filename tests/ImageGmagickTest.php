<?php

namespace rockunit;

use rock\imagine\Image;

/**
 * @group image
 */
class ImageGmagickTest extends AbstractImageTest
{
    protected function setUp()
    {
        if (!class_exists('\Gmagick')) {
            $this->markTestSkipped('Skipping ImageGmagickTest, Gmagick is not installed');
        } else {
            Image::setImagine(null);
            Image::$driver = Image::DRIVER_GMAGICK;
            parent::setUp();
        }
    }

    public function testFrame()
    {
        $frameSize = 5;
        $original = Image::getImagine()->open($this->imageFile);
        $originalSize = $original->getSize();
        $img = Image::frame($this->imageFile, $frameSize, '666', null);
        $size = $img->getSize();

        $this->assertEquals($size->getWidth(), $originalSize->getWidth() + ($frameSize * 2));
    }

    protected function isFontTestSupported()
    {
        return true;
    }
}
