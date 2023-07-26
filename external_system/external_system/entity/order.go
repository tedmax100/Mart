package entity

type Order struct {
	UserID       int
	ProductID    int
	OrderNumber  string
	Quantity     int
	ShippingCost int
	Payment      string
	Price        string
	Status       string
	Printed      bool
}
