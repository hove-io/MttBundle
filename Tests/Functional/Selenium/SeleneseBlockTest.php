<?php

namespace CanalTP\MttBundle\Tests;

class SeleneseBlockTest extends \PHPUnit_Extensions_SeleniumTestCase
{
    public static $seleneseDirectory;

    public static $browsers = array(
        array(
            'name' => 'Firefox on Windows7x64',
            'browser' => '*firefox C:\Users\usrrec-selcli\AppData\Local\Mozilla Firefox\firefox.exe',
            'host' => '10.2.16.22',
            'port' => 4444
        )
    );

    protected function setUp()
    {
        $this->captureScreenshotOnFailure = false;

        self::$seleneseDirectory = dirname(__FILE__). DIRECTORY_SEPARATOR . 'SeleneseNewBag';
        //Directory 'SeleneseBag' needs to be straighten up
        $baseUrl = 'http://nmm-ihm.ci.dev.canaltp.fr';
        //$baseUrl = 'http://nmm.local/app_dev.php';

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
