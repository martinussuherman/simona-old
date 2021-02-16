<?php

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * NotFoundMiddleware
 *
 * Processes the 404s
 */
class NotFoundMiddleware implements MiddlewareInterface
{
    /**
     * The route has not been found
     *
     * @returns bool
     */
    public function beforeNotFound(Event $event, Micro $application)
    {
        $route = $application->router->getMatchedRoute();
        $request = $application->request;

        //$application->logger->addItem('request_method', $request->getMethod());
        //$application->logger->addArray('request_param_', $_REQUEST);

        //$application->logger->warning('Not Found');

        $payload = [
            'code' => 404,
            'status' => Utils::MESSAGE_NOT_FOUND,
            'message' => 'Action Not Found'
        ];

        $application->response->setJsonContent($payload);
        $application->response->send();

        return false;
    }

    /**
     * Calls the middleware
     *
     * @param Micro $application
     *
     * @returns bool
     */
    public function call(Micro $application)
    {
        return true;
    }
}