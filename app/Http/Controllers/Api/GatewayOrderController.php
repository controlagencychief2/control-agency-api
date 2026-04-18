<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GatewayOrder;
use Illuminate\Http\Request;

class GatewayOrderController extends Controller
{
    // POST /api/gateway-orders
    public function store(Request $request)
    {
        $validated = $request->validate([
            'gateway' => 'required|string|max:50',
            'description' => 'required|string',
            'meeting_id' => 'nullable|string|max:100',
            'assigned_by' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['order_ref'] = 'ORDER-' . now()->format('YmdHis') . '-' . mt_rand(100, 999);
        $validated['status'] = 'pending';

        $order = GatewayOrder::create($validated);

        return response()->json([
            'success' => true,
            'data' => $order,
        ], 201);
    }

    // GET /api/gateways/{gateway}/orders?status=pending
    public function indexByGateway(Request $request, $gateway)
    {
        $status = $request->query('status');

        $query = GatewayOrder::where('gateway', $gateway);
        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'gateway' => $gateway,
            'data' => $orders,
        ]);
    }

    // PATCH /api/gateway-orders/{id}
    public function update(Request $request, $id)
    {
        $order = GatewayOrder::findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        if (($validated['status'] ?? null) === 'completed' && !$order->completed_at) {
            $validated['completed_at'] = now();
        }

        $order->update($validated);

        return response()->json([
            'success' => true,
            'data' => $order->fresh(),
        ]);
    }
}
