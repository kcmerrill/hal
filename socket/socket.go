package socket

import (
	"fmt"
	"github.com/gorilla/websocket"
	"github.com/kcmerrill/hal/connection"
	"github.com/kcmerrill/hal/message"
	log "github.com/kcmerrill/snitchin.go"
	"net/http"
)

var upgrader = websocket.Upgrader{
	ReadBufferSize:  1024,
	WriteBufferSize: 1024,
	CheckOrigin: func(r *http.Request) bool {
		/* We don't know your domain, so let it through ... for now */
		return true
	},
}

/* Handle incoming connections */
func registerConnection(msg chan *message.Message) func(w http.ResponseWriter, r *http.Request) {
	return func(w http.ResponseWriter, r *http.Request) {
		/* Upgrade our incoming message to a websocket */
		conn, err := upgrader.Upgrade(w, r, nil)
		if err != nil {
			/* Bummer, there was an error ... */
			log.ERROR(fmt.Sprintf("%q", err.Error()))
		} else {
			connection.Register(conn, msg)
		}
	}
}

/* Start up our socket server! */
func Boot(port int, msgs chan *message.Message) {
	/* Handle all of our incoming connections */
	http.HandleFunc("/ws", registerConnection(msgs))

	/* Start our WS server */
	err := http.ListenAndServe(fmt.Sprintf(":%d", port), nil)
	if err != nil {
		/* Bummer .. we can't start our WS server */
		log.ERROR(fmt.Sprintf("Unable to start websocker server on port %d", port))
	} else {
		log.INFO(fmt.Sprintf("Listening to web socket requests on port %d", port))
	}

}
