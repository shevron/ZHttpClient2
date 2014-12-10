<?php

namespace ZHttpClient2Test;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * Test context class, used for initializing the test environment and accessing
 * global configuration
 *
 * @package ProletarierTest
 */
class TestContext
{
    /**
     * @var array
     */
    protected static $config = array();

    public static function init()
    {
        ini_set('error_reporting', E_ALL);

        $files = array(__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php');
        foreach ($files as $file) {
            if (file_exists($file)) {
                $loader = require $file;
                break;
            }
        }

        if (! isset($loader)) {
            throw new \RuntimeException(
                'vendor/autoload.php could not be found. Did you run `php composer.phar install`?'
            );
        }

        /* @var $loader \Composer\Autoload\ClassLoader */
        $loader->add('ZHttpClient2Test\\', __DIR__);
    }

    /**
     * Get a new service manager object
     *
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        $serviceManager = new ServiceManager(new ServiceManagerConfig(
            isset(static::$config['service_manager']) ? static::$config['service_manager'] : array()
        ));
        $serviceManager->setService('ApplicationConfig', static::$config);
        $serviceManager->setFactory('ServiceListener', 'Zend\Mvc\Service\ServiceListenerFactory');

        /** @var $moduleManager \Zend\ModuleManager\ModuleManager */
        $moduleManager = $serviceManager->get('ModuleManager');
        $moduleManager->loadModules();
        return $serviceManager;
    }
}

TestContext::init();
