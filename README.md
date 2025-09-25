# Implementing Two-Factor-Authentication with andrej-griniuk plugin

## Intro
Here is a working example using [andrej-griniuk](https://github.com/andrej-griniuk/cakephp-two-factor-auth/tree/master) plugin. Only the important core files are in here. Other standard Cakephp 5 files (e.g. vendor folder) are not shown/included in this github. Important core files to take note of are :

1. src/Application.php
2. src/Controller/AppController.php
3. src/Controller/UsersController.php
4. templates/Users

<br/>

## Step 1 : Install Plugin
Install [Cakephp 5](https://book.cakephp.org/5/en/quickstart.html) with the [Authentication plugin](https://book.cakephp.org/authentication/3/en/index.html). From [andrej-griniuk](https://github.com/andrej-griniuk/cakephp-two-factor-auth/tree/master) guide, install the andrej-griniuk plugin by

```bash
composer require andrej-griniuk/cakephp-two-factor-auth
```

<br/>

## Step 2 : Add Field
Add a field called `secret` to your users table. This field name can be changed in the config, but for starters, keep it at this name. See [TwoFactorFormAuthenticator.php](https://github.com/andrej-griniuk/cakephp-two-factor-auth/blob/master/src/Authenticator/TwoFactorFormAuthenticator.php) for more info

```sql
ALTER TABLE `users` ADD `secret` VARCHAR(255) NULL;
```

<br/>

## Step 3 : Load Plugin
In `src/Controller/AppController.php`, load them as such

```php
// in src/Controller/AppController.php
public function initialize()
{
    parent::initialize();
    
    $this->loadComponent('Authentication.Authentication');
    $this->loadComponent('TwoFactorAuth.TwoFactorAuth');
}
```
<br/>

Load the plugin in `src/Application.php`

```php
// in src/Application.php
public function bootstrap(): void
{
    ... other code
    
    $this->addPlugin('TwoFactorAuth');
    
    ... other code
}
```

Also in `src/Application.php`. Add this code to `getAuthenticationService` function

```php
// in src/Application.php
public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
{
    ... other code
    
    $fields = [
        'username' => 'email',
        'password' => 'password'
    ];
    $authenticationService->loadAuthenticator('TwoFactorAuth.TwoFactorForm', [
        'fields' => $fields,
        'loginUrl' => Router::url([
            'prefix' => false,
            'plugin' => null,
            'controller' => 'Users',
            'action' => 'login',
        ]),
        'identifier' => [
            'Authentication.Password' => [
                'fields' => $fields,
                'resolver' => [
                    'className' => 'Authentication.Orm',
                    'userModel' => 'Users',
                ],
            ],
        ],
    ]);

    ... other code
}
```
> [!NOTE]
> In here, the field that I am using is `email` instead of `username`

<br/>

> [!TIP]
> If you are facing plugin loading issue, check your `config/plugins.php` file to make sure this plugin isn't loaded in there.

<br/>

## Step 4 : Template Files
Add the template files in `templates/Users`.

<br/>

## Step 5 : Usage
A short guide. Please play around with it yourself

Add a new user at `/Users/add`. You will need to modify this line of code in `src/Controller/UsersController.php` so you can call this `add` function without authentication. Remember to remove it later, if not, public can add users without authentication!

```php
$this->Authentication->addUnauthenticatedActions(['login', 'verify', 'add' ]);
```

> [!CAUTION]
> Leaving 'add' to `addUnauthenticatedActions` will be a major security flaw. Remove it for production environment.


<br/>

Login with that newly created account. Goto `/Users/add2factorCode` to add the `secret` to it. You will need the Authentication app for this

<br/>

Logout, and login again. After keying in your username and password at `Users/login`, you will be redirected to the new page `/Users/verify` to verify the 6-digit code from the Authentication app. Login will only be successful after this 2 factor authentication.

<br/>

## Feedback
Please feel free to ask any question, or feedback if something needs fixing/improvement.

