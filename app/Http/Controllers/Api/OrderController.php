<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;

class OrderController extends Controller
{
    private TracerInterface $tracer;
    private SpanInterface $rootSpan;

    public function __construct()
    {
        global $tracer, $rootSpan;
        $this->tracer = $tracer;
        $this->rootSpan = $rootSpan;
    }
    public function store(Request $request): JsonResponse
    {
        Log::info('test');
        $date = date('d/m/Y h:i:s a', time());

        $this->rootSpan->setAttribute('foo', 'bar');
        $this->rootSpan->updateName('OrderController\\store dated ' . $date);

        $parent = $this->tracer->spanBuilder("訂單開始")->startSpan();
        $scope = $parent->activate();
        try {
            $child = $this->tracer->spanBuilder("訂單寫入")->startSpan();
            $child->setAttribute('name', $request->name);
            $child->setAttribute('amount', $request->amount);
            usleep(rand(100000, 200000));
//                $order = new Order([
//                    'name' => $request->name,
//                    'amount' => $request->amount
//                ]);
//                $order->save();
            $child->end();
        } finally {
            $parent->end();
            $scope->detach();
        }

        // 回傳新增訂單的回應
        return response()->json(['message' => 'Order created successfully']);
    }

    public function pay($id): JsonResponse
    {
        $date = date('d/m/Y h:i:s a', time());

        $this->rootSpan->setAttribute('foo', 'bar');
        $this->rootSpan->setAttribute('Kishan', 'Sangani');
        $this->rootSpan->setAttribute('foo1', 'bar1');
        $this->rootSpan->updateName('HelloController\\index dated ' . $date);

        $parent = $this->tracer->spanBuilder("支付訂單完整流程")->startSpan();
        $scope = $parent->activate();
        try {
            $child = $this->tracer->spanBuilder("支付訂單")->startSpan();
            // 在這裡處理支付訂單的邏輯
            // 根據訂單 ID 從資料庫中獲取相應的訂單
//            $order = Order::find($id);
//
//            if (!$order) {
//                return response()->json(['message' => 'Order not found'], 404);
//            }
//            // 處理支付邏輯
//            $order->pay = 1;
//            $order->save();
            $child->end();
            $child = $this->tracer->spanBuilder("物流")->startSpan();
            // todo (post api) or (ship function)
            $child->end();
        } finally {
            $parent->end();
            $scope->detach();
        }

        // 回傳支付訂單的回應
        return response()->json(['message' => 'Order paid successfully', 'pay' => 1]);
    }

    public function cancel($id): JsonResponse
    {
        // 在這裡處理取消訂單的邏輯
        // 根據訂單 ID 從資料庫中獲取相應的訂單
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // 處理取消邏輯
        // ...

        // 回傳取消訂單的回應
        return response()->json(['message' => 'Order canceled successfully', 'order' => $order->id]);
    }

    public function ship($id): JsonResponse
    {
        // 在這裡處理貨運
        // 根據訂單 ID 從資料庫中獲取相應的訂單
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        // 處理邏輯

        // 回傳取消訂單的回應
        return response()->json(['message' => 'Order canceled successfully', 'order' => $order]);
    }
}
