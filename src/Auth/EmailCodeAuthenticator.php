<?php
declare(strict_types=1);

namespace App\Auth;

use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Cake\Mailer\Mailer;
use Psr\Http\Message\ServerRequestInterface;
use Cake\Log\Log;

class EmailCodeAuthenticator extends AbstractAuthenticator
{

    static function sendEmailWithCode(){

    }







    /**
     * Attempt to authenticate the request using an API token.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        debug($request);

        // Example: read token from header
        $token = $request->getHeaderLine('X-Api-Token');
        if (empty($token)) {
            // No token provided, credentials missing
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING);
        }

        // Look up user by token (you could call your UsersTable, or external API)
        $user = $this->_findUserByToken($token);
        if (!$user) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID, [
                'message' => 'Invalid API token'
            ]);
        }

        // Success — $user is an array or object representing the user identity
        return new Result($user, Result::SUCCESS);
    }

    /**
     * Example helper: finds a user by token.
     * You need to replace with your own logic.
     *
     * @param string $token
     * @return array|null
     */
    protected function _findUserByToken(string $token): ?array
    {
        // Example stub — replace with actual DB lookup or API call
        if ($token === 'SECRET1234') {
            return [
                'id' => 1,
                'username' => 'apiuser',
                'role' => 'api',
            ];
        }
        return null;
    }
}
