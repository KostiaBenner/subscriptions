<?php

namespace Nikservik\Subscriptions\CloudPayments;

use Illuminate\Support\Facades\Log;
use Nikservik\Subscriptions\CloudPayments\ApiResponse;

class PaymentApiResponse extends ApiResponse
{
    protected $errorCodes = [
        5051 => 'insufficient_funds',
        5082 => 'incorrect_cvv',
        5036 => 'restricted_card',
        5062 => 'restricted_card',
        5014 => 'incorrect_card',
        5015 => 'incorrect_card',
        5006 => 'incorrect_card',
        5054 => 'incorrect_card',
        5001 => 'contact_issuer',
        5003 => 'contact_issuer',
        5004 => 'contact_issuer',
        5005 => 'contact_issuer',
        5007 => 'contact_issuer',
        5019 => 'contact_issuer',
        5033 => 'contact_issuer',
        5034 => 'contact_issuer',
        5041 => 'contact_issuer',
        5043 => 'contact_issuer',
        5204 => 'contact_issuer',
        5206 => 'contact_issuer',
        5207 => 'contact_issuer',
        5057 => 'contact_issuer',
        5065 => 'contact_issuer',
        5013 => 'try_later',
        5030 => 'try_later',
        5096 => 'try_later',
        5091 => 'try_later_or_use_other',
        5092 => 'try_later_or_use_other',
        5063 => 'use_other',
        5300 => 'use_other',
        5031 => 'use_other',
        5012 => 'use_other',
    ];

    public function isSuccessful()
    {
        return $this->Success 
            and ($this->Status == 'Completed' or $this->Status == 'Authorized');
    }

    public function getErrorMessage()
    {
        Log::debug($this->data);
        if (! $this->Model || ! array_key_exists($this->ReasonCode, $this->errorCodes))
            return 'errors.undefined';

        return 'errors.'.$this->errorCodes[$this->ReasonCode];
    }

    public function need3dSecure()
    {
        return $this->Success === false and $this->PaReq and $this->AcsUrl;
    }
}