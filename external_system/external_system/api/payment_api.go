package api

import (
	"net/http"
	"otel_demo/external_system/entity"

	"github.com/gin-gonic/gin"
)

type PaymentService struct {
}

func NewPaymentService() *PaymentService {
	return &PaymentService{}
}

func (p *PaymentService) InitiatePaymentHandler(c *gin.Context) {
	var paymentReqInfo entity.Payment
	if err := c.ShouldBindJSON(&paymentReqInfo); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	initResponse := paymentReqInfo.InitiatePayment(c.Request.Context())
	if initResponse.Error != "" {
		c.JSON(http.StatusInternalServerError, initResponse)
		return
	}
	c.JSON(http.StatusOK, initResponse)
}
