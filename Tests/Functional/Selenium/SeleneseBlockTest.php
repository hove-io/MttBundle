<?php

namespace CanalTP\JourneyBundle\Tests;

class SeleneseBlockTest extends \PHPUnit_Extensions_SeleniumTestCase
{
    public static $seleneseDirectory;

    public static $browsers = array(
        array(
            'name' => 'Firefox on Windows',
            'browser' => '*firefox
                          C:\Program Files (x86)\Mozilla Firefox\firefox.exe',
            'host' => '10.2.0.176',
            // 'port' => 4444
        )
    );

    protected function setUp()
    {
        $this->captureScreenshotOnFailure = false;

        self::$seleneseDirectory = dirname(__FILE__). DIRECTORY_SEPARATOR . 'SeleneseBag';
        $baseUrl = 'http://iussaad.dev.canaltp.fr/';

        $this->setBrowserUrl($baseUrl);
        $this->setSpeed(150);
        $this->setTimeout(200);

        $this->start();
    }

    public function testSeleneses()
    {
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