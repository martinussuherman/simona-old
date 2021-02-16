<?php
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Router;
use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Security;
use Phalcon\Flash\Direct as FlashDirect;
use \Firebase\JWT\JWT;

class ContentTypeValidationMiddleware implements MiddlewareInterface
{
    public function beforeExecuteRoute(Event $event, Micro $application)
    {
        if (in_array($application->request->getMethod(), ['POST', 'PUT'])
            and ($application->request->getHeader('Content-Type') != 'application/json' &&
            substr($application->request->getHeader('Content-Type'), 0, strlen('multipart/form-data')) !== 'multipart/form-data')) {
            throw new CustomErrorException(400, 'Only application/json is accepted for Content-Type in POST requests.');
        }

        return true;
    }

    public function call(Micro $application)
    {
        return false;
    }
}