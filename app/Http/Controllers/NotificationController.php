<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationModel;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notification counts via AJAX for real-time updates
     */
    public function getCounts()
    {
        try {
            $pendingLoanCount = NotificationModel::getPendingLoanCount();
            $pendingWithdrawalCount = NotificationModel::getPendingWithdrawalCount();
            return response()->json([
                'success' => true,
                'data' => [
                    'pending_loan_count' => $pendingLoanCount,
                    'pending_withdrawal_count' => $pendingWithdrawalCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notification counts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification details for dropdown/modal
     */
    public function getDetails()
    {
        try {
            $pendingLoans = NotificationModel::getPendingLoans();
            $pendingWithdrawals = NotificationModel::getPendingWithdrawals();
            return response()->json([
                'success' => true,
                'data' => [
                    'pending_loans' => $pendingLoans,
                    'pending_withdrawals' => $pendingWithdrawals
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notification details: ' . $e->getMessage()
            ], 500);
        }
    }
}
