<?php
namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        // Ambil data dengan relasi dan paginate (10 per halaman)
        $dataBilling = billing::paginate(10);

        return view('billing.billing', compact('dataBilling'));
    }
}