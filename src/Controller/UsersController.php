<?php
declare(strict_types=1);

namespace App\Controller;
use Authentication\Controller\Component\AuthenticationComponent;
use Cake\Event\EventInterface;

use Cake\I18n\DateTime;
use Cake\Mailer\Mailer;
use RobThree\Auth\TwoFactorAuthException;


/**
 * Users Controller
 *
 * @property AuthenticationComponent $Authentication
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \TwoFactorAuth\Controller\Component\TwoFactorAuthComponent $TwoFactorAuth
 */
class UsersController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // Allow login and add
        $this->Authentication->addUnauthenticatedActions(['login', 'verify' ]);

    }

    public function initialize(): void
    {
        parent::initialize();

    }


    /**
     * @return \Cake\Http\Response|null|void Renders view
     * @throws TwoFactorAuthException
     */
    public function login()
    {

        $result = $this->Authentication->getResult();

        if ($result->isValid()) {
            // If the user is logged in send them away.
            $target = $this->Authentication->getLoginRedirect() ?? '/index';

            return $this->redirect($target);
        }

        // display error if user submitted and authentication failed
        if ($this->request->is('post') && !$result->isValid()) {

            if ($result->getStatus() == \TwoFactorAuth\Authenticator\Result::TWO_FACTOR_AUTH_FAILED) {
                // One time code was entered and it's invalid
                $this->Flash->error('Invalid 2FA code');

                return $this->redirect(['action' => 'verify']);
            } elseif ($result->getStatus() == \TwoFactorAuth\Authenticator\Result::TWO_FACTOR_AUTH_REQUIRED) {
                // One time code is required and wasn't yet entered - redirect to the verify action
                return $this->redirect(['action' => 'verify']);
            } else {
                $this->Flash->error('Invalid username or password');
            }

            $this->Flash->error(__('Invalid username or password'));
        }

    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result->isValid()) {
            $this->Authentication->logout();
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }



    /** Adds 2fa secret to user database
     * @return \Cake\Http\Response|null|void Renders view
     * @throws TwoFactorAuthException
     */
    public function add2factorCode()
    {

        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->get($this->Authentication->getIdentity()->id);

        if ($this->request->is('post') ) {

            $requestData = $this->request->getData();
            $receivedCode = $requestData['code'];
            $secret = $requestData['secret'];

            // Verify the code & secret
            $results = $this->TwoFactorAuth->verifyCode(secret: $secret, code: $receivedCode);

            if($results) {
                // Code is valid. Save this secret in user-table database
                $user->secret = $secret;
                $saveResults = $this->Users->save($user);

                if($saveResults) {
                    $this->Flash->success('Secret has been saved for your account. 2FA is now enabled for your account');
                    return $this->redirect(['controller' => 'Users', 'action' => 'index']);
                } else {
                    $this->Flash->error(__('Unable to update user with 2FA secret. Please try again.'));
                }
            } else {
                $this->Flash->error(__('Invalid code. Try again.'));
            }
        } else {

            // Use previous secret stored in database, or create new secret
            $secret = $user->secret ?? $this->TwoFactorAuth->createSecret();
        }

        $this->set('secret', $secret);
        $secretDataUri = $this->TwoFactorAuth->getQRCodeImageAsDataUri(label: 'CakePHP:' . $user->email, secret: $secret);
        $this->set('secretDataUri', $secretDataUri);


    }


    public function verify()
    {
        // This action is only needed to render a vew with one time code form
    }




    /**
     * @param int $length
     * @return string
     */
    private function generateRandomNumbers(int $length = 6): string
    {
        if ($length <= 0) {
            $length = 6;
        }
        $max = (10 ** $length) - 1;
        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    public function sendAuthCodeToEmail()
    {

        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->get($this->Authentication->getIdentity()->id);

        // Generate a random 6 digit code, and save it to user database
        $user->secret_email_code = $this->generateRandomNumbers(6);
        $user->secret_email_code_generation_time = DateTime::now()->format('U');
        $saveResults = $this->Users->save($user);
        debug($saveResults);

        if($saveResults) {

            $mailer = new Mailer('default');
            $mailer->setFrom(['support@bricatta.com' => 'Bricatta'])
                ->setTo('support@bricatta.com')
                ->setSubject('Login Code')
                ->deliver('Your login code is : ' . $user->secret_email_code);
        } else {
            $this->Flash->error(__('Unable to update user with 2FA secret. Please try again.'));
        }


        $this->disableAutoRender();

    }

}
