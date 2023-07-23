package entity

import (
	"context"
	"errors"
	"math/rand"
	"time"

	"github.com/google/uuid"
	"go.opentelemetry.io/otel/trace"
)

type Payment struct {
	// 要知道是哪個用戶正在進行付款，以便在需要時能將用戶聯繫起來，也可能需要為該用戶創建支付記錄。
	UserId int `form:"user_id" json:"user_id" xml:"user_id"  binding:"required"`
	// 知道用戶正在購買的商品可以幫助您跟踪銷售和庫存
	ProductId int `form:"product_id" json:"product_id" xml:"product_id"  binding:"required"`
	// 知道用戶正在購買的商品可以幫助您跟踪銷售和庫存
	// 商品數量將與單價相乘以計算出總金額，也可能需要更新庫存
	Quantity int `form:"quantity" json:"quantity" xml:"quantity"  binding:"required"`

	//您需要知道訂單的總價格以便可以請求相應的款項
	Price int `form:"price" json:"price" xml:"price"  binding:"required"`

	// 訂單編號是每個訂單的唯一標識，用於跟踪和確認訂單的狀態。
	OrderNumber string `form:"order_number" json:"order_number" xml:"order_number"  binding:"required"`

	// 付款方式，信用卡, xxx
	Payment string `form:"payment" json:"payment" xml:"payment"  binding:"required"`
}

type PaymentInitiationResponse struct {
	PaymentStatus   PaymentStatus `json:"paymentStatus"`
	TransactionID   string        `json:"transactionId"`
	TransactionTime time.Time     `json:"transactionTime"`
	Error           string        `json:"error,omitempty"`
}

// InitiatePayment, 用戶選擇好了支付方式後, 成立Payment以便後續處理
func (p *Payment) InitiatePayment(ctx context.Context) PaymentInitiationResponse {
	span := trace.SpanFromContext(ctx)
	defer span.End()

	// ...初始化支付逻辑
	// 會創建payment對象, 通常裡面會有對應的信用卡支付或第三方支付的對象(stripe, paypal, 綠界...)
	r := rand.Intn(10)
	time.Sleep(time.Second*1 + time.Duration(r)*time.Microsecond)

	if randBool() {
		return PaymentInitiationResponse{
			PaymentStatus:   Failed,
			TransactionID:   uuid.New().String(),
			TransactionTime: time.Now(),
			Error:           errors.New("server is broken").Error(),
		}
	}
	return PaymentInitiationResponse{
		PaymentStatus:   Initiated,
		TransactionID:   uuid.New().String(),
		TransactionTime: time.Now(),
	}

}

func (p *Payment) ProcessPayment() bool {
	// ...處理第三方支付結束的邏輯
	// 會更新對應的支付狀態與訂單狀態
	return true
}

func randBool() bool {
	rand.Seed(time.Now().UnixNano())
	return rand.Float32() > 0.33
}
