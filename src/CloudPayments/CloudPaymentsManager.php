<?php

namespace Nikservik\Subscriptions\CloudPayments;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

    public function apiTest(): ApiResponse
    {
        return new ApiResponse($this->requestCpApi('test'));
    }

    public function paymentsCardsCharge(CardChargeRequest $bill): PaymentApiResponse
    {
        return new PaymentApiResponse(
            $this->requestCpApi('payments/cards/charge', $bill->toArray()) 
        );
    }

    public function paymentsCardsAuth(CardChargeRequest $bill): PaymentApiResponse
    {
        return new PaymentApiResponse(
            $this->requestCpApi('payments/cards/auth', $bill->toArray()) 
        );
    }

    public function paymentsCardsPost3ds(Post3dsRequest $bill): PaymentApiResponse
    {
        return new PaymentApiResponse(
            $this->requestCpApi('payments/cards/post3ds', $bill->toArray()) 
        );
    }

    public function paymentsTokensCharge(TokenChargeRequest $bill): PaymentApiResponse
    {
        return new PaymentApiResponse(
            $this->requestCpApi('payments/tokens/charge', $bill->toArray()) 
        );
    }

    public function paymentsVoid($transactionId): ApiResponse 
    {
        return new ApiResponse(
            $this->requestCpApi('payments/void', ['TransactionId' => $transactionId]) 
        );
    }

    public function paymentsRefund($transactionId, $amount): ApiResponse
    {
        return new ApiResponse(
            $this->requestCpApi('payments/refund', [
                'TransactionId' => $transactionId,
                'Amount' => $amount,
            ]) 
        );
    }

    public function validateSecrets(Request $request): bool
    {
        return $this->verifyNotificationRequest($request);
    }

    public function verifyNotificationRequest(Request $request): bool
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

    protected function requestCpApi(string $url, array $params=[]): array
    {
        $response = Http::timeout(10)
            ->withBasicAuth($this->publicId, $this->apiSecret)
            ->post($this->apiUrl.$this->withLeadingSlash($url), ['json' => $params]);

        return $response->successful() ? json_decode($response->body(), true) : [];
    }

    protected function withLeadingSlash(string $url): string
    {
        return $url[0] == '/' ? $url : '/'.$url;
    }
}