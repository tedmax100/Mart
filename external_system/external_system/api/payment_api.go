package api

import (
	"context"
	"errors"
	"net/http"
	"otel_demo/external_system/entity"

	"github.com/gin-gonic/gin"
	"go.opentelemetry.io/otel"
	"go.opentelemetry.io/otel/attribute"
	"go.opentelemetry.io/otel/codes"
	"go.opentelemetry.io/otel/trace"
)

type PaymentService struct {
}

func NewPaymentService() *PaymentService {
	return &PaymentService{}
}

func (p *PaymentService) InitiatePaymentHandler(c *gin.Context) {
	span := trace.SpanFromContext(c.Request.Context())
	defer span.End()
	traceId := span.SpanContext().TraceID()
	var paymentReqInfo entity.Payment
	if err := c.ShouldBindJSON(&paymentReqInfo); err != nil {
		span.RecordError(err)
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	initResponse := paymentReqInfo.InitiatePayment(c.Request.Context())
	initResponse.TraceId = traceId.String()

	p.bb(c.Request.Context())
	if initResponse.Error != "" {
		c.JSON(http.StatusInternalServerError, initResponse)
		return
	}

	c.JSON(http.StatusOK, initResponse)
}

func (p *PaymentService) bb(ctx context.Context) {
	tr := otel.Tracer("bb")
	_, span := tr.Start(ctx, "bar")
	span.AddEvent("bb", trace.WithAttributes(attribute.String("bb", "123")))
	span.SetAttributes(attribute.Key("testset").String("value"))
	defer span.End()
	span.RecordError(errors.New("errors"))
	span.SetStatus(codes.Error, errors.New("errors").Error())
}
