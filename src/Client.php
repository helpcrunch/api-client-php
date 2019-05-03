<?php

namespace Helpcrunch\PublicApi;

use \GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class Client extends GuzzleClient
{
    const INVALID_KEY_CODE = 401;
    const NOT_FOUND_CODE = 404;
    const TOO_MANY_REQUESTS = 429;
    const DEFAULT_DOMAIN = 'com';
    const DEFAULT_SCHEMA = 'https://';

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @param string $organizationDomain
     * @param string $privateKey
     */
    public function __construct(
        string $organizationDomain = null,
        string $privateKey = null
    ) {
        if (empty($organizationDomain)) {
            throw new \InvalidArgumentException('You need to specify your organization\'s domain');
        }
        if (empty($privateKey)) {
            throw new \InvalidArgumentException('You need to specify your organization\'s private API key');
        }
        if (!defined('HELPCRUNCH_PUBLIC_API_SCHEMA')) {
            define('HELPCRUNCH_PUBLIC_API_SCHEMA', static::DEFAULT_SCHEMA);
        }
        if (!defined('HELPCRUNCH_PUBLIC_API_DOMAIN')) {
            define('HELPCRUNCH_PUBLIC_API_DOMAIN', static::DEFAULT_DOMAIN);
        }

        parent::__construct([
            'base_uri' => HELPCRUNCH_PUBLIC_API_SCHEMA .
                $organizationDomain . '.helpcrunch.' .
                HELPCRUNCH_PUBLIC_API_DOMAIN .
                '/api/public/',
        ]);
        $this->headers = [
            'Authorization' => 'Bearer api-key="' . $privateKey . '"',
        ];
    }

    public function request($method, $uri = '', array $options = [])
    {
        $options['headers'] = $this->headers;
        try {
            $response = parent::request($method, $uri, $options);
        } catch (ClientException $exception) {
            switch ($exception->getCode()) {
                case self::INVALID_KEY_CODE:
                    throw new \InvalidArgumentException('Invalid HelpCrunch API private key or organization domain');
                    break;
                case self::TOO_MANY_REQUESTS:
                    throw new \InvalidArgumentException(
                        'You are make too much or too big queries.' .
                        'You can check limits here: https://docs.helpcrunch.com/backend-api-reference.html#limitations'
                    );
                    break;
                case self::NOT_FOUND_CODE:
                    $response = false;
                    break;
                default:
                    throw $exception;
                    break;
            }
        }

        return $response;
    }

    /**
     * @param string|null $userId
     * @param int|null $id
     * @param string|null $email
     * @return Customer|null
     */
    public function getCustomer(string $userId = null, int $id = null, string $email = null) {
        $customer = new Customer($this, [
            'user_id' => $userId,
            'id' => $id,
            'email' => $email,
        ]);

        return $customer->load() ? $customer : null;
    }

    /**
     * @param string $userId
     * @return Customer
     */
    public function getCustomerByUserId(string $userId) {
        return $this->getCustomer($userId);
    }

    /**
     * @param int $id
     * @return Customer
     */
    public function getCustomerById(int $id) {
        return $this->getCustomer(null, $id);
    }

    /**
     * @param string $email
     * @return Customer
     */
    public function getCustomerByEmail(string $email) {
        return $this->getCustomer(null, null, $email);
    }
}
