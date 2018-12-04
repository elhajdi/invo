<?php
// die("dsflkjsdlfjs".PHP_EOL);
use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

// dispaly errors
ini_set('display_errors',1);
error_reporting(E_ALL);
// init time zone
date_default_timezone_set ( 'UTC' );

define('ROOT_PATH', dirname(__FILE__));
define('PATH_LIBRARY', ROOT_PATH . '/../app/library/');
define('PATH_MODELS', ROOT_PATH . '/../app/models/');
define('PATH_SERVICES', ROOT_PATH . '/../app/services/');
define('PATH_RESOURCES', ROOT_PATH . '/../app/resources/');
define('PATH_UTILS', ROOT_PATH . '/../app/utils/');

$config = include ROOT_PATH . "/../app/config/config.php";
// var_dump(ROOT_PATH);die;
set_include_path(
    ROOT_PATH . PATH_SEPARATOR . get_include_path()
);
// Required for app configuration
// global  $config;

// Required for phalcon/incubator
include ROOT_PATH . "/../vendor/autoload.php";

// Use the application autoloader to autoload the classes
// Autoload the dependencies found in composer
$loader = new \Phalcon\Loader();

$loader->registerDirs(
    array(
        ROOT_PATH,
        PATH_SERVICES,
        PATH_LIBRARY,
        PATH_MODELS,
    )
);

$loader->register();

$di = new FactoryDefault();

Di::reset();

// Add any needed services to the DI here
/**
 * configuration
 */
$di->set('config', function() use ($config) {
	
    return $config;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function () use ($config) {
    // return new DbAdapter($config->database->toArray());

    $eventsManager = new EventsManager();

    // $logger = new FileAdapter(APP_PATH."/app/logs/db-debug.log");

    // // Listen all the database events
    // $eventsManager->attach(
    //     "db:beforeQuery",
    //     function ($event, $connection) use ($logger) {
    //         $logger->log(
    //             $connection->getSQLStatement(),
    //             Logger::ERROR
    //         );
    //     }
    // );

    $connection = new DbAdapter($config->database->toArray());

    // Assign the eventsManager to the db adapter instance
    $connection->setEventsManager($eventsManager);

    return $connection;
});

$di->set('cache', function () use ($config) {
    // Cache data for 2 days
    $dataCache = new \Phalcon\Cache\Frontend\Data(array(
        "lifetime" => $config->cache_lifetime,
    ));

     //Create the Cache setting memcached connection options
    $cache = new \Phalcon\Cache\Backend\Apc($dataCache, array(
        'host' => 'localhost',
        'port' => 11211,
        'persistent' => false
    ));

    return $cache;
});

Di::setDefault($di);


