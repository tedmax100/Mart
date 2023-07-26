package entity

import (
	"context"
	"errors"
	"math/rand"
	"time"

	"go.opentelemetry.io/otel/trace"
)

/*
type EMail struct {
	Sender   string
	Receiver string
	Body     string
}*/

func EMailOrder(ctx context.Context, orer Order) error {
	span := trace.SpanFromContext(ctx)
	defer span.End()

	r := rand.Intn(10)
	time.Sleep(time.Second*1 + time.Duration(r)*time.Microsecond)

	if randBool() {
		return errors.New("mail server error")
	}
	return nil
}
