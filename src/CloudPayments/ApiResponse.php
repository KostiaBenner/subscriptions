<?php

namespace Nikservik\Subscriptions\CloudPayments;

class ApiResponse
{
    protected $responseData;

    function __construct(array $responseData)
    {
        $this->data = $responseData;
    }

    public function __get($name)
    {
        if (array_key_exists('Model', $this->data) 
            and array_key_exists($name , $this->data['Model']))
            return $this->data['Model'][$name];

        if (array_key_exists($name , $this->data))
            return $this->data[$name];

        return null;
    }

    public function isSuccessful()
    {
        return $this->Success;
    }

    public function getErrorMessage()
    {
        return $this->isSuccessful() ? null : 'errors.failed';
    }
}