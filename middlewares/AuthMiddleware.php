<?php
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Router;
use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Security;
use Phalcon\Flash\Direct as FlashDirect;
use \Firebase\JWT\JWT;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Extract user and token from Authentication header if present
     */
    public function beforeExecuteRoute(Event $event, Micro $application)
    {
        $request = $application->request;
        $jwtSecretToken = $application->config['jwt_secret_key'];
        $requestHeaders = $request->getHeaders();

        if (array_key_exists('Authorization', $requestHeaders) && strlen($requestHeaders['Authorization']) > 3) {
            $authorizationHeader = $requestHeaders['Authorization'];
            $authHeaders = explode(" ", $authorizationHeader);

            if (count($authHeaders) == 2 && $authHeaders[0] == 'Bearer') {
                $bearerToken = $authHeaders[1];

                try {
                    $jwt = JWT::decode($bearerToken, $jwtSecretToken, ['HS256']);
                } catch (\Exception $e) {
                    throw new CustomErrorException(440, 'Invalid authentication token');
                }

				if (!isset($jwt->id) || !isset($jwt->acc) || !isset($jwt->exp))
                    throw new CustomErrorException(440, 'Invalid authentication token');

				$token = Token::findFirst([
					'conditions' => 'id = :id:',
					'bind' => ['id' => $jwt->id],
				]);

                if (empty($token))
                    throw new CustomErrorException(440, 'Authentication token not found');

                if ($token->state == Token::STATE_REVOKED)
                    throw new CustomErrorException(440, 'Authentication token is revoked');

                if ($jwt->exp < time())
                    throw new CustomErrorException(440, 'Authentication token is expired');

				$acc = Account::findFirst([
					'conditions' => 'id = :id:',
					'bind' => ['id' => $jwt->acc],
				]);

                if (!empty($acc)) {
					if ($acc->role != $jwt->role) {
						throw new CustomErrorException(440, 'Invalid account role');
					} else {
						$application->shared->account = $acc;
						$application->shared->token = $token;
						$application->shared->jwt = $jwt;
					}
                } else {
                    throw new CustomErrorException(440, 'Account not found');
                }
            } else {
                throw new CustomErrorException(440, 'Invalid authentication header');
            }
        }

        return true;
    }

    public function call(Micro $application)
    {
        return false;
    }
}