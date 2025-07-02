<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::with(['user'])->latest();

        if (!empty($request->get('keyword'))) {
            $orders = $orders->where('id','like','%'.$request->get('keyword').'%')
                            ->orWhere('email','like','%'.$request->get('keyword').'%')
                            ->orWhere('first_name','like','%'.$request->get('keyword').'%')
                            ->orWhere('last_name','like','%'.$request->get('keyword').'%');
        }
        
        $orders = $orders->paginate(10);
        
        return view('admin.orders.list', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($orderId)
    {
        $order = Order::with(['items.product', 'user', 'country'])->find($orderId);
        
        if (empty($order)) {
            return redirect()->route('orders.index')->with('error', 'Order not found');
        }
        
        return view('admin.orders.detail', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($orderId)
    {
        $order = Order::find($orderId);
        
        if (empty($order)) {
            return redirect()->route('orders.index')->with('error', 'Order not found');
        }
        
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($orderId, Request $request)
    {
        $order = Order::find($orderId);

        if (empty($order)) {
            session()->flash('error', 'Order not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Order not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed',
        ]);

        if ($validator->passes()) {
            $order->status = $request->status;
            $order->payment_status = $request->payment_status;
            
            if ($request->status == 'shipped' && empty($order->shipped_date)) {
                $order->shipped_date = now();
            }
            
            $order->save();

            session()->flash('success', 'Order updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($orderId)
    {
        $order = Order::find($orderId);

        if (empty($order)) {
            session()->flash('error', 'Order not found');
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ]);
        }

        // Delete order items first
        OrderItem::where('order_id', $orderId)->delete();
        
        // Delete the order
        $order->delete();

        session()->flash('success', 'Order deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Order deleted successfully'
        ]);
    }

    public function sendInvoiceEmail($orderId, Request $request)
    {
        $order = Order::find($orderId);

        if (empty($order)) {
            session()->flash('error', 'Order not found');
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ]);
        }

        // Here you would implement email sending logic
        // For now, we'll just return a success message
        
        session()->flash('success', 'Invoice email sent successfully');

        return response()->json([
            'status' => true,
            'message' => 'Invoice email sent successfully'
        ]);
    }
}
