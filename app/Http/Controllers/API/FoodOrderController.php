<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FoodOrder;
use App\Models\FoodOrderItem;
use App\Models\MenuItem;
use App\Models\Notification;
use App\Models\Restaurant;
use App\Models\User;
use App\Mail\OrderInvoiceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FoodOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = FoodOrder::with('restaurant:id,name', 'user:id,name,email', 'items', 'deliveryRider:id,name,email');

        if ($user->role_id == 3) {
            $query->where('user_id', $user->id);
        } elseif ($user->role_id == 2) {
            $query->where('restaurant_id', $user->restaurant_id ?? 0);
        } elseif ($user->role_id == 4) {
            // Rider: only orders ready for pickup (unassigned) or assigned to this rider
            $query->where(function ($q) use ($user) {
                $q->where('delivery_rider_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->whereNull('delivery_rider_id')->where('status', 'ready');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->get('per_page', 15);
        $orders = $query->orderByDesc('created_at')->paginate($perPage);
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'delivery_address' => 'required|string',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $restaurant = Restaurant::findOrFail($request->restaurant_id);
        $subtotal = 0;
        $orderItems = [];

        foreach ($request->items as $row) {
            $menuItem = MenuItem::with('menuCategory')->findOrFail($row['menu_item_id']);
            if ($menuItem->menuCategory->restaurant_id != $restaurant->id) abort(422, 'Invalid menu item');
            if (!$menuItem->is_available) abort(422, 'Item not available: ' . $menuItem->name);
            $qty = (int) $row['quantity'];
            $unitPrice = $menuItem->price;
            $orderItems[] = [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $qty,
            ];
            $subtotal += $unitPrice * $qty;
        }

        $deliveryFee = $restaurant->delivery_fee ?? 0;
        $tax = 0;
        $total = $subtotal + $deliveryFee + $tax;

        $order = FoodOrder::create([
            'order_number' => FoodOrder::generateOrderNumber(),
            'restaurant_id' => $restaurant->id,
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'delivery_address' => $request->delivery_address,
            'customer_phone' => $request->customer_phone ?? $request->user()->email,
            'notes' => $request->notes,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'tax' => $tax,
            'total' => $total,
        ]);

        foreach ($orderItems as $item) {
            $item['food_order_id'] = $order->id;
            FoodOrderItem::create($item);
        }

        // Notify customer: order placed
        Notification::createForUser(
            $order->user_id,
            'order_created',
            'Order placed',
            'Your order ' . $order->order_number . ' has been placed successfully.',
            ['order_id' => $order->id, 'order_number' => $order->order_number]
        );

        // Notify vendor(s) / hotel manager: new order (include customer name)
        $customerName = $request->user()->name ?? 'A customer';
        $vendorUserIds = User::where('restaurant_id', $order->restaurant_id)->where('role_id', 2)->pluck('id');
        foreach ($vendorUserIds as $uid) {
            Notification::createForUser(
                $uid,
                'order_created',
                'New order',
                $customerName . ' placed an order (' . $order->order_number . ').',
                ['order_id' => $order->id, 'order_number' => $order->order_number]
            );
        }

        $order->load('items');
        return response()->json($order, 201);
    }

    public function show(Request $request, FoodOrder $foodOrder)
    {
        $user = $request->user();
        if ($user->role_id == 3 && $foodOrder->user_id != $user->id) abort(404);
        if ($user->role_id == 2 && $foodOrder->restaurant_id != $user->restaurant_id) abort(404);
        if ($user->role_id == 4 && $foodOrder->delivery_rider_id !== null && $foodOrder->delivery_rider_id != $user->id) abort(404);
        if ($user->role_id == 4 && $foodOrder->delivery_rider_id === null && $foodOrder->status !== 'ready') abort(404);
        $foodOrder->load(['restaurant', 'items', 'payments', 'user:id,name,email', 'deliveryRider:id,name,email']);
        return response()->json($foodOrder);
    }

    public function updateStatus(Request $request, FoodOrder $foodOrder)
    {
        $user = $request->user();
        if ($user->role_id == 2 && $foodOrder->restaurant_id != $user->restaurant_id) abort(404);
        if ($user->role_id == 4) {
            if ($foodOrder->delivery_rider_id !== null && $foodOrder->delivery_rider_id != $user->id) {
                abort(404);
            }
            $request->validate(['status' => 'required|in:out_for_delivery,delivered']);
            if ($request->status === 'delivered' && $foodOrder->delivery_rider_id != $user->id) {
                abort(403, 'Only the assigned rider can mark this order as delivered.');
            }
        } else {
            $request->validate(['status' => 'required|in:confirmed,preparing,ready,out_for_delivery,delivered,cancelled']);
        }

        $oldStatus = $foodOrder->status;
        if ($request->status == 'out_for_delivery' && $user->role_id == 4) {
            $foodOrder->update(['status' => 'out_for_delivery', 'delivery_rider_id' => $user->id]);
        } else {
            $foodOrder->update(['status' => $request->status]);
        }

        // Notify customer when status changes
        if ($oldStatus !== $request->status) {
            $statusLabel = ucfirst(str_replace('_', ' ', $request->status));
            Notification::createForUser(
                $foodOrder->user_id,
                'order_status_changed',
                'Order status updated',
                'Your order ' . $foodOrder->order_number . ' is now ' . $statusLabel . '.',
                ['order_id' => $foodOrder->id, 'order_number' => $foodOrder->order_number, 'status' => $request->status]
            );
        }

        // When vendor marks order "ready", notify all riders so they see it in Deliveries
        if ($request->status === 'ready' && $user->role_id != 4) {
            $riderIds = User::where('role_id', 4)->pluck('id');
            foreach ($riderIds as $riderId) {
                Notification::createForUser(
                    $riderId,
                    'order_ready_for_delivery',
                    'Order ready for delivery',
                    'Order ' . $foodOrder->order_number . ' from ' . $foodOrder->restaurant->name . ' is ready for pickup.',
                    ['order_id' => $foodOrder->id, 'order_number' => $foodOrder->order_number]
                );
            }
        }

        // When order is marked "delivered", email PDF invoice to the customer
        if ($request->status === 'delivered' && $foodOrder->user_id) {
            try {
                $foodOrder->load(['restaurant', 'user', 'items']);
                $customerEmail = $foodOrder->user->email ?? null;
                if ($customerEmail) {
                    Mail::to($customerEmail)->send(new OrderInvoiceMail($foodOrder));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json($foodOrder);
    }
}
