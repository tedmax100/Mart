<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use RealRashid\SweetAlert\Facades\Alert;

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

    public function store(): JsonResponse
    {
        Log::info('ORDER_CREATED');

        // 回傳創立訂單的回應
        if (mt_rand(0, 1)) {
            $order = Order::factory()->create();
            Log::info('order successfully');
            return response()->json(['message' => 'Order created successfully', 'data' => $order]);
        } else {
            Log::info('order failed');
            return response()->json(['message' => 'Order created failed'], 500);
        }
    }

    public function pay($id): JsonResponse
    {
        Log::info('ORDER_PAY');
        $date = date('d/m/Y h:i:s a', time());

        $this->rootSpan->updateName('HelloController\\index dated ' . $date);

        $parent = $this->tracer->spanBuilder("支付訂單完整流程")->startSpan();
        $parent1 = $parent->activate();

        Log::info('Activated Complete Payment Span', [
            'trace_id' => $this->rootSpan->getContext()->getTraceId(),
            'span_id' => $parent->getContext()->getSpanId(),
        ]);

        $child = $this->tracer->spanBuilder("搜尋支付訂單")->startSpan();
        Log::info('Activated Payment Span', [
            'trace_id' => $this->rootSpan->getContext()->getTraceId(),
            'span_id' => $child->getContext()->getSpanId(),
        ]);
        // 在這裡處理支付訂單的邏輯
        // 根據訂單 ID 從資料庫中獲取相應的訂單
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $child->end();
        // 處理支付邏輯
        // 在 Context 中建立新的 Span
        $span = $this->tracer->spanBuilder("支付訂單")->startSpan();
        Log::info('Activated Pay Span', [
            'trace_id' => $this->rootSpan->getContext()->getTraceId(),
            'span_id' => $span->getContext()->getSpanId(),
        ]);
        $headers['traceparent'] = sprintf(
            '%s-%s-%s-%02x',
            '00',
            $span->getContext()->getTraceId(),
            $span->getContext()->getSpanId(),
            $span->getContext()->getTraceFlags(),
        );
        // 執行 POST 請求並攜帶自訂 Header
        $response = Http::withHeaders($headers)->post(
            'http://payment:8080/initPayment',
            $order->toArray()
        );

        // 完成 API 邏輯後結束 Span
        $span->end();
        Log::info('Detached Pay Span', [
            'trace_id' => $this->rootSpan->getContext()->getTraceId(),
            'span_id' => $span->getContext()->getSpanId(),
        ]);
        // 返回 API 回應

        $reqPayment = $response->json();
        Log::info('Detached Payment Span', [
            'trace_id' => $this->rootSpan->getContext()->getTraceId(),
            'span_id' => $child->getContext()->getSpanId(),
        ]);

        $orderStatus = 0;
//        $child->setAttribute('paymentStatus', $reqPayment['paymentStatus']);
        if ($reqPayment['paymentStatus'] == 'Initiated') {
            $orderStatus = 1;
            $order->status = 'pay';
            $order->save();
        }
        $parent->end();
        $parent1->detach();

        Log::info('Detached Complete Payment Span', [
            'trace_id' => $this->rootSpan->getContext()->getTraceId(),
            'span_id' => $parent->getContext()->getSpanId(),
        ]);

        // 回傳支付訂單的回應
        if ($orderStatus == 1) {
            Log::info('ORDER_PAYED');
            return response()->json(['message' => 'Order paid successfully', 'orderStatus' => $orderStatus]);
        } else {
            Log::info('ORDER_PAY_FAILED');
            return response()->json(['message' => 'Order paid failed', 'orderStatus' => $orderStatus]);
        }
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
    public function addCart(): JsonResponse
    {
        //先暫訂使用Admin使用者ID
        $user_id = 1;
        $user = User::findOrFail($user_id);

        //隨機取一個商品ID
        $product = Product::inRandomOrder()->first();

        $exists = $user->cart()
            ->where('product_id', $product->id)
            ->get();

        if ($exists->count()) {
            return response()->json(['message' => 'You haved already added']);
        }

        $cart = new Cart();
        $cart->user_id = $user_id;
        $cart->product_id = $product->id;
        $cart->quantity = $request->quantity ?? 1;

        if ($cart->save()) {
            return response()->json(['message' => 'Product added to cart!']);
        } else {
            return response()->json(['message' => 'Something went wrong']);
        }
    }
}
