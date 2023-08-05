package main

import (
	"context"
	"html/template"
	"log"
	"os"
	"otel_demo/external_system/api"

	"go.opentelemetry.io/contrib/instrumentation/github.com/gin-gonic/gin/otelgin"
	"go.opentelemetry.io/otel/exporters/zipkin"

	"github.com/gin-gonic/gin"
	"go.opentelemetry.io/otel"
	"go.opentelemetry.io/otel/attribute"
	"go.opentelemetry.io/otel/propagation"
	"go.opentelemetry.io/otel/sdk/resource"
	sdktrace "go.opentelemetry.io/otel/sdk/trace"
)

// var tracer = otel.Tracer("gin-server")
var logger = log.New(os.Stderr, "zipkin-example", log.Ldate|log.Ltime|log.Llongfile)

func main() {
	tp, err := initTracer()
	if err != nil {
		log.Fatal(err)
	}
	defer func() {
		if err := tp.Shutdown(context.Background()); err != nil {
			log.Printf("Error shutting down tracer provider: %v", err)
		}
	}()
	//bb := MiddlewareBuilder{Tracer: tp.Tracer("test")}
	router := gin.Default()
	//router.Use(bb.Build())

	router.Use(otelgin.Middleware(
		"payment_service",
		otelgin.WithTracerProvider(tp),
	))

	tmplName := "user"
	tmplStr := "user {{ .name }} (id {{ .id }})\n"
	tmpl := template.Must(template.New(tmplName).Parse(tmplStr))
	router.SetHTMLTemplate(tmpl)

	paymentApi := api.NewPaymentService()
	emailApi := api.NewEMailService()
	router.POST("initPayment", paymentApi.InitiatePaymentHandler)
	router.POST("sendMailNofity", emailApi.SendNotify)
	_ = router.Run(":8080")
}

func initTracer() (*sdktrace.TracerProvider, error) {
	exporter, err := zipkin.New(
		"http://collector:9411/api/v2/spans",
		zipkin.WithLogger(logger),
	)
	if err != nil {
		return nil, err
	}
	resources, err := resource.New(
		context.Background(),
		resource.WithAttributes(
			attribute.String("service.name", "payment-app"),
			attribute.String("library.language", "go"),
		),
	)
	if err != nil {
		log.Printf("Could not set resources: ", err)
	}

	tp := sdktrace.NewTracerProvider(
		sdktrace.WithSampler(sdktrace.AlwaysSample()),
		sdktrace.WithSyncer(exporter),
		sdktrace.WithResource(resources),
	)
	otel.SetTracerProvider(tp)
	otel.SetTextMapPropagator(propagation.NewCompositeTextMapPropagator(propagation.TraceContext{}, propagation.Baggage{}))
	return tp, nil
}
