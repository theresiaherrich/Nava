<?php

namespace App\Http\Controllers;

use App\Support\Dummy\Operator;

class DashboardOperatorController extends Controller
{
    /** Dashboard Operator (desain 2.01). */
    public function index()
    {
        return view('dashboard-operator.index', ['data' => Operator::dashboard()]);
    }
}
