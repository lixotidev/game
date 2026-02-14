<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function __construct(protected PaystackService $paystackService)
    {
    }

    /**
     * Get wallet balance.
     */
    public function balance(Request $request)
    {
        return response()->json([
            'balance' => $request->user()->wallet_balance,
        ]);
    }

    /**
     * Get transaction history.
     */
    public function transactions(Request $request)
    {
        $transactions = $request->user()->transactions()
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    /**
     * Initialize deposit.
     */
    public function initializeDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $user = $request->user();
        $reference = (string) Str::uuid();

        try {
            $data = [
                'amount' => $request->amount,
                'email' => $user->email,
                'reference' => $reference,
                'metadata' => [
                    'user_id' => $user->id,
                    'type' => 'deposit',
                ],
            ];

            $response = $this->paystackService.initializeTransaction($data);

            // Create pending transaction
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'status' => 'pending',
                'paystack_reference' => $reference,
                'paystack_access_code' => $response['data']['access_code'],
                'description' => 'Wallet deposit via Paystack',
            ]);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to initialize deposit: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verify deposit.
     */
    public function verifyDeposit(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
        ]);

        $transaction = Transaction::where('paystack_reference', $request->reference)
            ->where('status', 'pending')
            ->firstOrFail();

        try {
            $response = $this->paystackService.verifyTransaction($request->reference);

            if ($response['data']['status'] === 'success') {
                DB::transaction(function () use ($transaction) {
                    $transaction->markAsCompleted();
                    $transaction->user->creditWallet($transaction->amount);
                });

                return response()->json([
                    'message' => 'Deposit successful',
                    'balance' => $transaction->user->wallet_balance,
                ]);
            }

            return response()->json(['message' => 'Transaction not successful'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to verify deposit: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Request withdrawal.
     */
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500',
            'account_number' => 'required|string',
            'bank_code' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user->hasSufficientBalance($request->amount)) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        try {
            return DB::transaction(function () use ($user, $request) {
                // Debit wallet immediately to lock funds
                $user->debitWallet($request->amount);

                // Create withdrawal record
                $withdrawal = Withdrawal::create([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'account_number' => $request->account_number,
                    'bank_code' => $request->bank_code,
                    'account_name' => $request->account_name,
                    'status' => 'pending',
                ]);

                // Create transaction record
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => $request->amount,
                    'status' => 'pending',
                    'description' => 'Withdrawal request to ' . $request->account_name,
                ]);

                return response()->json([
                    'message' => 'Withdrawal request submitted successfully',
                    'withdrawal' => $withdrawal,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to submit withdrawal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get list of banks.
     */
    public function getBanks()
    {
        try {
            $response = $this->paystackService.getBanks();
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch banks'], 500);
        }
    }
}
