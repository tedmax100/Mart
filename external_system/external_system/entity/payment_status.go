package entity

import (
	"encoding/json"
	"errors"
)

type PaymentStatus int

const (
	Initiated PaymentStatus = iota
	Processed
	Failed
	Succeeded
)

func (s PaymentStatus) String() string {
	return [...]string{"Initiated", "Processed", "Failed", "Succeeded"}[s]
}

func (s PaymentStatus) MarshalJSON() ([]byte, error) {
	return json.Marshal(s.String())
}

func (s *PaymentStatus) UnmarshalJSON(data []byte) error {
	var str string
	if err := json.Unmarshal(data, &str); err != nil {
		return err
	}

	switch str {
	case "Initiated":
		*s = Initiated
	case "Processed":
		*s = Processed
	case "Failed":
		*s = Failed
	case "Succeeded":
		*s = Succeeded
	default:
		return errors.New("Invalid PaymentStatus")
	}

	return nil
}
