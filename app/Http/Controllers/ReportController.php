<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['contingent', 'event'])->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('reports.index', compact('payments'));
    }
}
