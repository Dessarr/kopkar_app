<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\PengajuanPinjaman;
use App\Models\PengajuanPenarikan;

class NotificationModel extends Model
{
    protected $table = 'notifications';

    /**
     * Get count of pending loan applications
     * Based on user level (admin sees pending, operator sees approved)
     */
    public static function getPendingLoanCount()
    {
        $userLevel = Auth::guard('admin')->user()->level ?? 'admin';
        
        if (in_array($userLevel, ['pinjaman', 'admin', 'pusat'])) {
            // Admin level - count pending applications
            return PengajuanPinjaman::pending()->count();
        } else {
            // Operator level - count approved applications
            return PengajuanPinjaman::approved()->count();
        }
    }

    /**
     * Get count of pending withdrawal requests
     * Based on user level (admin sees pending, operator sees approved)
     */
    public static function getPendingWithdrawalCount()
    {
        $userLevel = Auth::guard('admin')->user()->level ?? 'admin';
        
        if (in_array($userLevel, ['pinjaman', 'admin', 'pusat'])) {
            // Admin level - count pending withdrawals
            return PengajuanPenarikan::pending()->count();
        } else {
            // Operator level - count approved withdrawals
            return PengajuanPenarikan::approved()->count();
        }
    }

    /**
     * Get all pending loan applications for display
     */
    public static function getPendingLoans()
    {
        $userLevel = Auth::guard('admin')->user()->level ?? 'admin';
        
        if (in_array($userLevel, ['pinjaman', 'admin', 'pusat'])) {
            return PengajuanPinjaman::pending()->get();
        } else {
            return PengajuanPinjaman::approved()->get();
        }
    }

    /**
     * Get all pending withdrawal requests for display
     */
    public static function getPendingWithdrawals()
    {
        $userLevel = Auth::guard('admin')->user()->level ?? 'admin';
        
        if (in_array($userLevel, ['pinjaman', 'admin', 'pusat'])) {
            return PengajuanPenarikan::pending()->get();
        } else {
            return PengajuanPenarikan::approved()->get();
        }
    }


}