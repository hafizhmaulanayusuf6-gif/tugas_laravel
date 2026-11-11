<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['member', 'items'])
            ->latest('id')
            ->paginate(10);

        return view('history.index', compact('orders'));
    }
}
