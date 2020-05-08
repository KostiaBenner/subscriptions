<?php

namespace Nikservik\Subscriptions\CloudPayments;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nikservik\Subscriptions\CloudPayments\ApiResponse;
use Nikservik\Subscriptions\CloudPayments\PaymentApiResponse;
use Nikservik\Subscriptions\CloudPayments\Receipt;


class CloudPaymentsManager 
{
    protected $publicId;
    protected $apiSecret;
    protected $apiUrl;

    public function __construct()
    {
        $this->publicId = config('cloudpayments.publicId');
        $this->apiSecret = config('cloudpayments.apiSecret');
        $this->apiUrl = config('cloudpayments.apiUrl');
    }

    public function apiTest()
    {
        return new ApiResponse($this->requestCpApi('test'));
    }

    public function paymentsCardsCharge(CardChargeRequest $bill)
    {
        return new PaymentApiResponse(
            $this->requestCpApi('payments/cards/charge', $bill->toArray()) 
        );
    }

    public function paymentsCardsAuth(CardChargeRequest $bill)
    {
        return new PaymentApiResponse(
            $this->requestCpApi('payments/cards/auth', $bill->toArray()) 
        );
    }

    public function paymentsCardsPost3ds(Post3dsRequest $bill)
    {
        return new PaymentApiResponse(
            $this->requestCpApi('payments/cards/post3ds', $bill->toArray()) 
        );
    }

    public function paymentsTokensCharge(TokenChargeRequest $bill)
    {
        return new PaymentApiResponse(
            $this->requestCpApi('payments/tokens/charge', $bill->toArray()) 
        );
    }

    public function paymentsVoid($transactionId)
    {
        return new ApiResponse(
            $this->requestCpApi('payments/void', ['TransactionId' => $transactionId]) 
        );
    }

    public function paymentsRefund($transactionId, $amount)
    {
        return new ApiResponse(
            $this->requestCpApi('payments/refund', [
                'TransactionId' => $transactionId,
                'Amount' => $amount,
            ]) 
        );
    }

    public function kktReceipt(Receipt $receipt)
    {
        return new ApiResponse(
            $this->requestCpApi('kkt/receipt', $receipt->toArray()) 
        );
    }

    public function kktReceiptStatusGet($id)
    {
        return new ApiResponse(
            $this->requestCpApi('kkt/receipt/status/get', ['Id' => $id]) 
        );
    }

    public function validateSecrets(Request $request)
    {
        return $this->verifyNotificationRequest($request);
    }

    public function verifyNotificationRequest(Request $request)
    {
        if (empty($secretCloudPayments = $request->header('Content-Hmac')))
            return false;

        $secret = base64_encode(
            hash_hmac(
                'sha256',
                file_get_contents('php://input'),
                config('cloudpayments.apiSecret'),
                true
            )
        );

        return $secret === $secretCloudPayments;
    }

    protected function requestCpApi(string $url, array $params=[])
    {
        $response = (new HttpClient())->request('POST', 
            $this->apiUrl.$this->withLeadingSlash($url), 
            [
                // 'headers' => ['Content-Type: application/x-www-form-urlencoded'],
                'auth' => [$this->publicId, $this->apiSecret],
                'json' => $params,
                'timeout' => 10,
            ]
        );

        return $response->getStatusCode() == 200 ? json_decode($response->getBody(), true) : [];
    }

    protected function withLeadingSlash($url)
    {
        return $url[0] == '/' ? $url : '/'.$url;
    }
}