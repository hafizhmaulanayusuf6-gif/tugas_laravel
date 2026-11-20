<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $orders = Order::with(['member', 'items'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($memberQuery) use ($search) {
                            $memberQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items', function ($itemQuery) use ($search) {
                            $itemQuery->where('product_name', 'like', "%{$search}%");
                        });

                    if (str_contains('pembeli umum', mb_strtolower($search))) {
                        $builder->orWhereNull('member_id');
                    }
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('history.index', compact('orders', 'search'));
    }

    public function receipt(Order $order): View
    {
        $order->load(['member', 'items']);

        return view('history.receipt', compact('order'));
    }
}
