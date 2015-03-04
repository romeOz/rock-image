<?php
namespace rockunit;

use League\Flysystem\Adapter\Local;
use rock\base\Alias;
use rock\file\FileManager;
use rock\helpers\FileHelper;
use rock\image\ImageProvider;
use rock\imagine\Image;

/**
 * @group image
 */
class ImageProviderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!function_exists('gd_info')) {
            $this->markTestSkipped('Skipping ImageGdTest, Gd not installed');
        } else {
            Image::setImagine(null);
            Image::$driver = Image::DRIVER_GD2;
            parent::setUp();
        }
    }

    public function test()
    {
        $config = [
            'adapter' => [
                'class' => FileManager::className(),
                'adapter' => new Local(Alias::getAlias('@rockunit/data/imagine')),
            ],
            'adapterCache' => [
                'class' => FileManager::className(),
                'adapter' => new Local(Alias::getAlias('@rockunit/runtime/cache')),
            ],
        ];
        $image = new ImageProvider($config);
        $src = $image->get('large.jpg', 50, 50);
        $this->assertSame('/assets/cache/50x50/large.jpg', $src);
        $this->assertTrue(file_exists(Alias::getAlias('@rockunit/runtime/cache/50x50/large.jpg')));

        // skip height
        $src = $image->get('large.jpg', 50);
        $this->assertSame('/assets/cache/50x360/large.jpg', $src);
        $this->assertTrue(file_exists(Alias::getAlias('@rockunit/runtime/cache/50x360/large.jpg')));

        // as is
        $src = $image->get('large.jpg');
        $this->assertSame('/assets/images/large.jpg', $src);
    }

    protected function isFontTestSupported()
    {
        $infos = gd_info();

        return isset($infos['FreeType Support']) ? $infos['FreeType Support'] : false;
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        FileHelper::deleteDirectory(Alias::getAlias('@rockunit/runtime'));
    }
}