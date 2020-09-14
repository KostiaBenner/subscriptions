<?php

namespace Tests\Subscriptions;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Nikservik\Subscriptions\Models\Payment;
use Tests\TestCase;

class CloudPaymentsControllerTest extends TestCase
{
    use DatabaseTransactions;


    public function testReceipt()
    {
        $payment = factory(Payment::class)->create(['remote_transaction_id' => 12345678]);

        $response = $this->postJson('api/cp/receipt', ['TransactionId' => 12345678, 'Url' => 'http://example.com/receipt'])
            ->assertStatus(200)
            ->assertJsonPath('code', 0);

        $this->assertEquals('http://example.com/receipt', $payment->refresh()->receipt_url);
    }
}