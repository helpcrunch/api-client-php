<?php

namespace Helpcrunch\PublicApi;

/**
 * @property int id
 * @property string user_id
 * @property string email
 * @property string name
 * @property string company
 * @property bool unsubscribed
 * @property array custom_data
 */
class Customer extends Entity {
    /**
     * @var string
     */
    protected static $endpoint = 'customers';

    public function load()
    {
        $response = $this->apiClient->request('GET', static::$endpoint, [
            'query' => $this->getSearchArguments(),
        ]);
        if ($response && ($body = $response->getBody()->getContents())) {
            $responseJson = json_decode($body, true);
            $this->fields = array_merge($this->fields, $responseJson);
            return true;
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getSearchArguments()
    {
        $search = [];
        foreach (['user_id', 'id', 'email'] as $field) {
            if ($this->fields[$field]) {
                $search[$field] = $this->fields[$field];
            }
        }
        if (empty($search)) {
            throw new \InvalidArgumentException('You need ID, user_id or email to load customer');
        }

        return $search;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getCustomDataItem(string $name)
    {
        return $this->fields['custom_data'][$name] ?? null;
    }

    /**
     * @param string $name
     * @param string|int $value
     * @return $this
     */
    public function setCustomDataItem($name, $value)
    {
        if (!isset($this->fields['custom_data']) || !is_array($this->fields['custom_data'])) {
            $this->fields['custom_data'] = [];
        }
        $this->fields['custom_data'][$name] = $value;

        return $this;
    }

    /**
     * @param array $customData
     * @return $this
     */
    public function updateCustomData(array $customData)
    {
        foreach ($customData as $field => $value) {
            $this->setCustomDataItem($field, $value);
        }

        return $this;
    }
}
