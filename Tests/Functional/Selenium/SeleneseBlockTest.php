<?php

namespace CanalTP\JourneyBundle\Tests;

class SeleneseBlockTest extends \PHPUnit_Extensions_SeleniumTestCase
{
    public static $seleneseDirectory;

    public static $browsers = array(
        array(
            'name' => 'Internet Explorer on Windows',
            'browser' => '*iexplore C:\Program Files\Internet Explorer\iexplore.exe',
            'host' => '10.2.16.20',
            'port' => 4444
        )
    );

    protected function setUp()
    {
        $this->captureScreenshotOnFailure = false;
        //Tests directory to straight up
        //self::$seleneseDirectory = dirname(__FILE__). DIRECTORY_SEPARATOR . 'SeleneseBag';
        self::$seleneseDirectory = dirname(__FILE__). DIRECTORY_SEPARATOR . 'SeleneseNewBag';
        $baseUrl = 'http://nmm.local';

        $this->setBrowserUrl($baseUrl);
        $this->setSpeed(50);
        // $this->setTimeout(10);

        $this->start();
    }

    public function testSeleneses()
    {
        self::shareSession(true);
        if ($handle = opendir(self::$seleneseDirectory)) {
            // $this->open('/journey');
            while (false !== ($entry = readdir($handle))) {
                if ('.' != $entry  && '..' != $entry) {
                    $this->runSelenese(self::$seleneseDirectory . DIRECTORY_SEPARATOR . $entry);
                }
            }
            closedir($handle);
        }
    }
}
