<?php

define('BASE_DIR', __DIR__);

// Load composer libraries
require_once BASE_DIR . '/vendor/autoload.php';

function dd($object)
{
    echo "<pre>";
    print_r($object);
    die();
}

#region Includes
use Phalcon\Loader;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Postgresql;
use Phalcon\Security;
use Phalcon\Security\Random;
#endregion

#region Application Config
require BASE_DIR . '/config/application.php';
#endregion

#region public vars
$random = new \Phalcon\Security\Random();
$requestId = $random->hex(8);
$sharedMem = new stdClass();
#endregion

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // should do a check here to match $_SERVER['HTTP_ORIGIN'] to a
    // whitelist of safe domains
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    die();
}

#region Loader
$loader = new Loader();

$loader->registerDirs(
[
    BASE_DIR . '/controllers/',
    BASE_DIR . '/exceptions/',
    BASE_DIR . '/middlewares/',
    BASE_DIR . '/models/',
    BASE_DIR . '/responses/',
    BASE_DIR . '/tools/',
]);

$loader->register();
#endregion

#region Dependency Injections
$di = new FactoryDefault();

// Set up di variables
$di->set(
    'flash',
    function () {
        $flash = new FlashDirect(
            [
                'error'   => 'alert alert-danger',
                'success' => 'alert alert-success',
                'notice'  => 'alert alert-info',
                'warning' => 'alert alert-warning',
            ]
        );

        return $flash;
    }
);

$di->set('random', function() use ($random){
    return $random;
});

$di->set('shared', function() use ($sharedMem){
    return $sharedMem;
});

$di->set('setShared', function($shared) use ($sharedMem){
    $sharedMem = $shared;
});

$di->set('db', function() use ($applicationConfig)
{
	return new Postgresql($applicationConfig['database']);
});

$di->set('config', function() use ($applicationConfig)
{
    return $applicationConfig;
});

$di->set('requestId', $requestId);

$di->set(
    'security',
    function () {
        $security = new Security();

        // Set the password hashing factor to 12 rounds
        $security->setWorkFactor(12);

        return $security;
    },
    true
);

$di->set(
    'die',
    function($statusCode, $message){
        $payload = [
            'success' => false,
            'message' => $message
        ];

        $this->response->setStatusCode($statusCode,$message);
        $this->response->setJsonContent($payload);
        $this->response->send();
        die();
        return false;
    }
);
#endregion

$app = new Micro($di);
$eventsManager = new Manager();

#region Registers
foreach ($applicationConfig['routes'] as $key => $routeConfig) {
    $collection = new Collection();
    $collection->setPrefix($routeConfig['prefix']);
    $collection->setHandler($routeConfig['controller'], TRUE);

    foreach ($routeConfig['paths'] as $key => $pathEntry) {
        $verb = $pathEntry['verb'];

        $collection->$verb(
            $pathEntry['path'],
            $pathEntry['method'],
            $pathEntry['name']
        );
    }

    $app->mount($collection);
}

foreach ($applicationConfig['middlewares'] as $joint => $middlewares) {
    foreach ($middlewares as $key => $middlewareClass) {
        $eventsManager->attach('micro', new $middlewareClass);
        $app->$joint(new $middlewareClass);
    }
}

// Special middleware: Logging Middleware. Same instance for before & after
// $loggingMiddleware = new LoggingMiddleware();
// $eventsManager->attach('micro', $loggingMiddleware);
// $app->before($loggingMiddleware);
// $app->after($loggingMiddleware);

$app->setEventsManager($eventsManager);

$app->error(
    function ($exception) use ($app) {

        if(get_class($exception) == 'ValidationException') {
            $app->response->setStatusCode(460, "Error");
            $app->response->setJsonContent([
                'success' => false,
                'message' => $exception->getMessages()
            ]);

            $app->response->send();
        } else if(get_class($exception) == 'CustomErrorException') {
            $app->response->setStatusCode($exception->getStatusCode(), "Error");
            $app->response->setJsonContent([
                'success' => false,
                'message' => $exception->getMessages()
            ]);

            $app->response->send();
        }else {
            error_log($exception);
            throw $exception;
        }
        return false;
    }
);
#endregion

$app->get('/', function ()
{
	$result = new stdClass();
	$result->name = $this->config['name'];
	$result->version = $this->config['version'];

	//$mtimes = array();
	//$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_DIR, FilesystemIterator::SKIP_DOTS));
	//foreach($objects as $fileInfo)
	//	array_push($mtimes, $fileInfo->getMTime());
	//rsort($mtimes);

	//if (count($mtimes) > 0)
	//	$result->last_updated = date('D, d-M-Y H:i:s T', $mtimes[0]);

	return new ObjectResponse($result);
});

$app->get('/version', function ()
{
	$result = new stdClass();
	$result->version = $this->config['version'];

	return new ObjectResponse($result);
});

$app->handle();