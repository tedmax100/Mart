<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
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

    public function pay($id): JsonResponse
    {
        Log::info('test');
        $date = date('d/m/Y h:i:s a', time());

        $this->rootSpan->setAttribute('foo', 'bar');
        $this->rootSpan->setAttribute('Kishan', 'Sangani');
        $this->rootSpan->setAttribute('foo1', 'bar1');
        $this->rootSpan->updateName('HelloController\\index dated ' . $date);

        $parent = $this->tracer->spanBuilder("支付訂單完整流程")->startSpan();
        Log::info('Activated Complete Payment Span', [
            'span_id' => $parent->getContext()->getSpanId(),
        ]);

        $child = $this->tracer->spanBuilder("支付訂單")->startSpan();
        Log::info('Activated Payment Span', [
            'span_id' => $child->getContext()->getSpanId(),
        ]);
        // 在這裡處理支付訂單的邏輯
        // 根據訂單 ID 從資料庫中獲取相應的訂單
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        // 處理支付邏輯
        // 在 Context 中建立新的 Span
        $span = $this->tracer->spanBuilder("支付訂單")->startSpan();
        Log::info('Activated Pay Span', [
            'trace_id' => $this->rootSpan->getContext()->getTraceId(),
            'span_id' => $span->getContext()->getSpanId(),
        ]);
        // 執行 POST 請求並攜帶自訂 Header
        $response = Http::withTrace2()->post(
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

        $child->setAttribute('paymentStatus', $reqPayment['paymentStatus']);
        if ($reqPayment['paymentStatus'] == 'initiated') {
            $order->status = 'pay';
            $order->save();
        }
        $child->end();
        $parent->end();

        Log::info('Detached Complete Payment Span', [
            'trace_id' => $this->rootSpan->getContext()->getTraceId(),
            'span_id' => $parent->getContext()->getSpanId(),
        ]);

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
