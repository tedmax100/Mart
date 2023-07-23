package api

import (
	"net/http"
	"otel_demo/external_system/entity"

	"github.com/gin-gonic/gin"
)

type EMailService struct {
}

func NewEMailService() *EMailService {
	return &EMailService{}
}

func (p *EMailService) SendNotify(c *gin.Context) {

	err := entity.EMailOrder(c.Request.Context(), entity.Order{})
	if err != nil {
		c.JSON(http.StatusInternalServerError, err)
		return
	}
	c.JSON(http.StatusOK, "notified")
}
