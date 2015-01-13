<?php namespace Prontotype\Plugins\HttpAuth;

use Exception;
use Prontotype\Config;

class HttpAuth
{
    public $type = 'basic';

    public $realm = 'Secured resource';

    private $username;

    private $password;

    public $config;

    private $user;

    /**
     * Creates new instance of Httpauth
     *
     * @param array $parameters     set realm, username and/or password as key
     * @param Illuminate\Config\Repository $config
     */

    public function __construct(Config $config)
    {
        // basic settings from config files
        $this->type = $config->get('httpauth.type');
        $this->realm = $config->get('httpauth.realm');
        $this->username = $config->get('httpauth.username');
        $this->password = $config->get('httpauth.password');
    
        // set user based on authentication type
        switch (strtolower($this->type)) {
            case 'digest':
                $this->user = new DigestUser;
                break;

            default:
                $this->user = new BasicUser;
                break;
        }

        if ( ! $this->username || ! $this->password) {
            throw new Exception('No username or password set for HttpAuthentication.');
        }
    }

    /**
     * Denies access for not-authenticated users
     *
     * @return void
     */
    public function secure()
    {
        if ( ! $this->validateUser($this->user)) {
            $this->denyAccess();
        }
    }

    /**
     * Checks for valid user
     *
     * @param  User $user
     * @return bool
     */
    private function validateUser(UserInterface $user)
    {
        return $user->isValid($this->username, $this->password, $this->realm);
    }

    /**
     * Checks if username/password combination matches
     *
     * @param  string  $username
     * @param  string  $password
     * @return boolean
     */
    public function isValid($username, $password)
    {
        return ($username == $this->username) && ($password == $this->password);
    }

    /**
     * Sends HTTP 401 Header
     *
     * @return void
     */
    private function denyAccess()
    {
        header('HTTP/1.0 401 Unauthorized');

        switch (strtolower($this->type)) {
            case 'digest':
                header('WWW-Authenticate: Digest realm="' . $this->realm .'",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($this->realm) . '"');
                break;

            default:
                header('WWW-Authenticate: Basic realm="'.$this->realm.'"');
                break;
        }

        throw new \Exception('HTTP/1.0 401 Unauthorized');
        // die('<strong>HTTP/1.0 401 Unauthorized</strong>');
    }
}
