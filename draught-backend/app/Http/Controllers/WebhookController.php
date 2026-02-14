<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(protected PaystackService $paystackService)
    {
    }

    public function paystack(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (!$this->paystackService->verifyWebhook($signature, $payload)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        Log::info('Paystack Webhook Received: ' . $event['event']);

        switch ($event['event']) {
            case 'charge.success':
                $this->handleChargeSuccess($event['data']);
                break;
            case 'transfer.success':
                $this->handleTransferSuccess($event['data']);
                break;
            case 'transfer.failed':
                $this->handleTransferFailed($event['data']);
                break;
            case 'transfer.reversed':
                $this->handleTransferFailed($event['data']);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleChargeSuccess(array $data)
    {
        $reference = $data['reference'];
        $transaction = Transaction::where('paystack_reference', $reference)->first();

        if ($transaction && $transaction->isPending()) {
            DB::transaction(function () use ($transaction) {
                $transaction->markAsCompleted();
                $transaction->user->creditWallet($transaction->amount);
                Log::info("Deposit confirmed for User ID: {$transaction->user_id}, Amount: {$transaction->amount}");
            });
        }
    }

    protected function handleTransferSuccess(array $data)
    {
        $transferCode = $data['transfer_code'];
        $withdrawal = Withdrawal::where('paystack_transfer_code', $transferCode)->first();

        if ($withdrawal && ($withdrawal->isPending() || $withdrawal->isProcessing())) {
            $withdrawal->update(['status' => 'completed', 'processed_at' => now()]);
            // Transaction linked to this was already marked pending, we can mark it completed if tracked
            Transaction::where('type', 'withdrawal')
                ->where('user_id', $withdrawal->user_id)
                ->where('amount', $withdrawal->amount)
                ->where('status', 'pending')
                ->latest()
                ->first()
                ?->markAsCompleted();
            
            Log::info("Withdrawal completed for User ID: {$withdrawal->user_id}, Amount: {$withdrawal->amount}");
        }
    }

    protected function handleTransferFailed(array $data)
    {
        $transferCode = $data['transfer_code'];
        $withdrawal = Withdrawal::where('paystack_transfer_code', $transferCode)->first();

        if ($withdrawal) {
            DB::transaction(function () use ($withdrawal) {
                $withdrawal->update(['status' => 'failed', 'admin_notes' => 'Transfer failed at Paystack']);
                // Refund user
                $withdrawal->user->creditWallet($withdrawal->amount);
                
                Transaction::where('type', 'withdrawal')
                    ->where('user_id', $withdrawal->user_id)
                    ->where('amount', $withdrawal->amount)
                    ->where('status', 'pending')
                    ->latest()
                    ->first()
                    ?->markAsFailed();
                
                Log::warning("Withdrawal failed and refunded for User ID: {$withdrawal->user_id}, Amount: {$withdrawal->amount}");
            });
        }
    }
}
