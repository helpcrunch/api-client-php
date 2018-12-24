<?php

namespace Helpcrunch\PublicApi;

class Entity {
    /**
     * @var string
     */
    protected static $endpoint;

    /**
     * @var Client
     */
    protected $apiClient;

    /**
     * @var array
     */
    public $fields = [];

    public function __construct(Client $apiClient, array $fields = [])
    {
        $this->apiClient = $apiClient;
        $this->fields = $fields;
    }

    public function save()
    {
        $this->apiClient->request('PATCH', static::$endpoint, [
            'body' => json_encode([$this->fields]),
        ]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * @param string $name
     * @param string|int $value
     * @return $this
     */
    public function __set(string $name, $value)
    {
        $this->fields[$name] = $value;

        return $this;
    }
}
