<?php

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * LoggingMiddleware
 *
 * Processes the 404s
 */
class LoggingMiddleware implements MiddlewareInterface
{
    protected $initialTime;
    protected $routeName;
    protected $routePattern;
    protected $allowed_headers = ['user-agent'];

    public function beforeExecuteRoute(Event $event, Micro $application)
    {
        $this->initialTime = microtime(TRUE);

        $route = $application->router->getMatchedRoute();
        $request = $application->request;

        $this->routeName = $route->getName();
        $this->routePattern = $route->getPattern();
        $headers = $this->item_whitelist_ignore_case($request->getHeaders(), $this->allowed_headers);

        $application->logger->addItem('route_pattern', $this->routePattern);
        $application->logger->addItem('route_name', $this->routeName);
        $application->logger->addItem('request_method', $request->getMethod());
        $application->logger->addArray('request_param_', $_REQUEST);
        $application->logger->addArray('request_header_', $headers);

        $application->logger->info('Start handing request');
    }

    public function afterHandleRoute(Event $event, Micro $application)
    {
        $endTime = microtime(TRUE);
        $delta = round($endTime - $this->initialTime, 3) * 1000;

        $application->logger->addItem('route_pattern', $this->routePattern);
        $application->logger->addItem('route_name', $this->routeName);
        $application->logger->addItem('processing_time', $delta);

        $application->logger->info('Finish handling request');
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

    private function item_whitelist_ignore_case($item, $expected_keys)
    {
        $result = [];
        $lower_expected_keys = array_map('strtolower', $expected_keys);

        foreach ($item as $key => $value) {
            if (in_array(strtolower($key), $lower_expected_keys)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}