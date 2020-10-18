<?php

namespace Nikservik\Subscriptions;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Nikservik\Subscriptions\CloudPayments\CardChargeRequest;
use Nikservik\Subscriptions\CloudPayments\PaymentApiResponse;
use Nikservik\Subscriptions\CloudPayments\Post3dsRequest;
use Nikservik\Subscriptions\CloudPayments\TokenChargeRequest;
use Nikservik\Subscriptions\Facades\CloudPayments;
use Nikservik\Subscriptions\Facades\Subscriptions;
use Nikservik\Subscriptions\Models\Payment;
use Nikservik\Subscriptions\Models\Subscription;
use Nikservik\Subscriptions\Models\Tariff;



class PaymentsManager 
{

    public function charge(Subscription $subscription)
    {
        if (! ($bill = $this->prepareTokenBill($subscription)))
            return false;

        $response = CloudPayments::paymentsTokensCharge($bill);

        if (! $response->isSuccessful())
            return false;

        return $this->savePayment($response->Model);
    }

    public function chargeByCrypt(User $user, Tariff $tariff, string $cardholderName, string $ip,  string $crypt)
    {
        $bill = new CardChargeRequest($tariff->price, $tariff->currency, 
            $cardholderName, $crypt, $ip, $user->id, $user->email,
            __('subscriptions::payments.charge', ['app' => Str::after(config('app.url'), '//')])
        );

        return $this->activateSubscriptionByResponse($user, $tariff, 
            CloudPayments::paymentsCardsCharge($bill));
    }

    public function authorizeByCrypt(User $user, string $cardholderName, string $ip,  string $crypt)
    {
        $bill = new CardChargeRequest(10, 'RUB', 
            $cardholderName, $crypt, $ip, $user->id, $user->email,
            __('subscriptions::payments.authorization', ['app' => Str::after(config('app.url'), '//')])
        );

        return $this->authorizeByResponse($user, 
            CloudPayments::paymentsCardsAuth($bill));
    }

    public function post3ds(User $user, Tariff $tariff, int $transactionId, string $paRes)
    {
        return $this->activateSubscriptionByResponse($user, $tariff, 
            CloudPayments::paymentsCardsPost3ds(
                new Post3dsRequest($transactionId, $paRes)
            ));
    }

    public function authorizePost3ds(User $user, int $transactionId, string $paRes)
    {
        return $this->authorizeByResponse($user,  
            CloudPayments::paymentsCardsPost3ds(
                new Post3dsRequest($transactionId, $paRes)
            ));
    }

    public function refund(Payment $payment)
    {
        $response = CloudPayments::paymentsRefund($payment->remote_transaction_id, $payment->amount);

        if (! $response->isSuccessful()) 
            return false;

        $payment->status = 'Refunded';
        $payment->save();
        return true;
    }

    protected function activateSubscriptionByResponse(User $user, Tariff $tariff, PaymentApiResponse $response)
    {
        if (! $response->isSuccessful() and ! $response->need3dSecure())
            return $response->getErrorMessage();

        if ($response->need3dSecure())
            return $response->Model;

        if (! $subscription = Subscriptions::activate($user, $tariff, true))
            return 'errors.failed';

        $this->saveToken($user, $response->Model);

        $this->savePayment(array_merge($response->Model, ['InvoiceId' => $subscription->id]));

        return $subscription;
    }

    protected function authorizeByResponse(User $user, PaymentApiResponse $response)
    {
        if (! $response->isSuccessful() and ! $response->need3dSecure())
            return $response->getErrorMessage();

        if ($response->need3dSecure())
            return $response->Model;

        $this->saveToken($user, $response->Model);

        CloudPayments::paymentsVoid($response->TransactionId);

        return true;
    }

    protected function prepareTokenBill(Subscription $subscription)
    {
        if (! $subscription->price > 0 or ! $subscription->prolongable)
            return false;

        if (! $subscription->user or ! $subscription->user->token)
            return false;

        return new TokenChargeRequest($subscription->price, $subscription->currency,
            $subscription->user->token, $subscription->user->id, $subscription->user->email, $subscription->id,
            __('subscriptions::payments.autocharge', ['app' => Str::after(config('app.url'), '//')])
        );
    }

    protected function savePayment($data)
    {
        $validator = Validator::make($data, [
            'AccountId' => 'required|numeric',
            'TransactionId' => 'required|numeric|unique:payments,remote_transaction_id',
            'CardLastFour' => 'required|numeric',
            'Amount' => 'required|numeric',
            'Currency' => 'required',
            'Status' => 'required',
            'InvoiceId' => 'required|numeric',
        ]);

        if ($validator->fails())  {
            Log::debug($validator->errors());
            return false;
        }

        // отправить чек

        return Payment::create([
            'subscription_id' => $data['InvoiceId'], 
            'user_id' => $data['AccountId'], 
            'remote_transaction_id' => $data['TransactionId'], 
            'card_last_digits' => $data['CardLastFour'],
            'amount' => $data['Amount'], 
            'currency' => $data['Currency'], 
            'status' => $data['Status'], 
        ]);
    }

    protected function saveToken(User $user, array $response)
    {
        $user->token = $response['Token'];
        $user->cardLastFour = $response['CardLastFour'];
        $user->save();
    }

    public function getSubscription($data)
    {
        if (array_key_exists('InvoiceId', $data) and $data['InvoiceId'])
            return Subscription::find($data['InvoiceId']);

        if (! array_key_exists('Data', $data))
            return false;

        $tariffData = json_decode($data['Data'], true);

        if (! array_key_exists('tariff_id', $tariffData))
            return false;

        return Subscriptions::activate(
                User::find($data['AccountId']),
                Tariff::find($tariffData['tariff_id'])
        );
    }

}