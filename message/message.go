package message

import (
	"encoding/json"
	"errors"
)

type Message struct {
	To        string `json:"to"`
	Msg       string `json:"msg"`
	Signature string `json:"signature,omitempty"`
	Status    string `json:"status,omitempty"`
	From      string `json:"from"`
}

func Open(msg []byte) (*Message, error) {
	m := &Message{}
	if err := json.Unmarshal(msg, m); err != nil {
		return m, errors.New("Unable to open the message")
	}
	return m, nil
}

func (m *Message) Type() string {

	if string(m.Msg[0]) == "/" {
		return "command"
	}

	if string(m.To[0]) == "@" {
		return "direct"
	}

	if string(m.To[0]) == "_" {
		return "system"
	}

	if string(m.To[0]) == "#" {
		return "channel"
	}

	return "??"
}

func (m *Message) SendOk() ([]byte, error) {
	m.Signature = ""
	if json, err := json.Marshal(m); err == nil {
		return json, nil
	}

	return []byte(""), errors.New("Unable to convert message to JSON")
}
