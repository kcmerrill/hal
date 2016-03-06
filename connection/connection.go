package connection

import (
	log "github.com/Sirupsen/logrus"
	"github.com/gorilla/websocket"
	"github.com/kcmerrill/hal/message"
)

var connections map[*connection]bool

func init() {
	connections = make(map[*connection]bool)
}

func Connections() map[*connection]bool {
	return connections
}

func Register(conn *websocket.Conn, msgs chan *message.Message) {
	c := &connection{WS: conn, Signature: "Guest", Msgs: msgs}
	connections[c] = true
	c.listen()
}

type connection struct {
	WS        *websocket.Conn
	Signature string
	Msgs      chan *message.Message
}

func (c *connection) Write(m *message.Message) {
	if msg, err := m.SendOk(); err == nil {
		c.WS.WriteMessage(1, msg)
	} else {
		log.Error(err.Error())
	}
}

func (c *connection) listen() {
	log.Info("New connection ...")
	for {
		/* Keep reading in messages for as long as necessary */
		_, msg, err := c.WS.ReadMessage()
		if err != nil {
			/* Here? Connection dropped ... unregister the connection */
			delete(connections, c)
			log.Info(c.Signature + " left")
			break
		} else {
			if m, err := message.Open(msg); err == nil {
				c.Signature = m.Signature
				c.Msgs <- m
			} else {
				log.Error("Unable to open message")
			}
		}
	}
}
