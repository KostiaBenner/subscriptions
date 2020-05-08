<?php

namespace Nikservik\Subscriptions\CloudPayments;

use Nikservik\Subscriptions\CloudPayments\ApiResponse;

class PaymentApiResponse extends ApiResponse
{
    protected $errorCodes = [
        5051 => 'insufficient-funds',
        5082 => 'incorrect-cvv',
        5036 => 'restricted-card',
        5062 => 'restricted-card',
        5014 => 'incorrect-card',
        5015 => 'incorrect-card',
        5006 => 'incorrect-card',
        5054 => 'incorrect-card',
        5001 => 'contact-issuer',
        5003 => 'contact-issuer',
        5004 => 'contact-issuer',
        5005 => 'contact-issuer',
        5007 => 'contact-issuer',
        5019 => 'contact-issuer',
        5033 => 'contact-issuer',
        5034 => 'contact-issuer',
        5041 => 'contact-issuer',
        5043 => 'contact-issuer',
        5204 => 'contact-issuer',
        5206 => 'contact-issuer',
        5207 => 'contact-issuer',
        5057 => 'contact-issuer',
        5065 => 'contact-issuer',
        5013 => 'try-later',
        5030 => 'try-later',
        5096 => 'try-later',
        5091 => 'try-later-or-use-other',
        5092 => 'try-later-or-use-other',
        5063 => 'use-other',
        5300 => 'use-other',
        5031 => 'use-other',
        5012 => 'use-other',
    ];

    public function isSuccessful()
    {
        return $this->Success 
            and ($this->Status == 'Completed' or $this->Status == 'Authorized');
    }

    public function getErrorMessage()
    {
        if (! $this->Model)
            return 'errors.undefined';

        return array_key_exists($this->ReasonCode, $this->errorCodes) 
            ? 'errors.'.$this->errorCodes[$this->ReasonCode]
            : 'errors.'.$this->ReasonCode;
    }

    public function need3dSecure()
    {
        return $this->Success === false and $this->PaReq and $this->AcsUrl;
    }
}