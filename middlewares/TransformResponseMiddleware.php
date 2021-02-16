<?php

use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class TransformResponseMiddleware implements MiddlewareInterface
{
    public function call(Micro $application)
    {
        $returnedValue = $application->getReturnedValue();

        if($returnedValue instanceof BaseResponse)
        {
            $content = $returnedValue->buildBody();
            $statusCode = $returnedValue->getStatusCode();
        }else if( $returnedValue instanceof Phalcon\Http\Response ){

            return true;
        }
        else
        {
            $content = [
                'status' => 'success',
                'data' => $returnedValue
            ];
            $statusCode = 200;
        }

        $application->response->setJsonContent($content);
        $application->response->setStatusCode($statusCode, "OK");
        $application->response->send();

        return true;
    }
}