package hal

import (
	"flag"
	"fmt"
	log "github.com/Sirupsen/logrus"
	"github.com/kcmerrill/hal/channel"
	"github.com/kcmerrill/hal/message"
	"github.com/kcmerrill/hal/socket"
	"github.com/kcmerrill/hal/users"
	"github.com/kcmerrill/hal/web"
)

var master_signature string

func Boot() {
	/* Setup our command line arguments */
	web_server := flag.Int("web", 80, "Port to run the webserver")
	socket_server := flag.Int("socket", 8080, "Port to run the websocket server")
	signature := flag.String("signature", "hal", "Master password to be used")
	workers := flag.Int("workers", 100, "How many workers to spin up")
	master_signature = *signature
	flag.Parse()

	log.Info("Good Morning Dave ...")

	/* TODO: Remove me */
	users.Register(&users.Info{
		Username:  "dave",
		Signature: master_signature,
		Channels:  map[string]int{},
	})

	/* Create a few channels */
	msgs := make(chan *message.Message)

	/* Get our message workers up and running */
	for x := 1; x <= *workers; x++ {
		go MessageWorker(x, msgs)
	}

	/* Start Hal's webserver in the background */
	go web.Boot(*web_server, msgs)

	/* Start Hal's websocket server */
	go socket.Boot(*socket_server, msgs)
}

func MessageWorker(id int, msgs chan *message.Message) {
	for {
		/* Grab a message off the channel */
		m := <-msgs

		if user, err := users.Fetch(m.Signature); err == nil {
			/* Quick logging */
			m.From = user.At()

			/* Depending on the type of message, do something with it */
			switch m.Type() {
			case "channel":
				channel.Broadcast(m)
				continue
			case "direct":
				//users.Message(m)
				//log.DEBUG("A direct message to " + m.To)
			default:
				log.Error("Unknown message type: " + m.Type())
				continue
			}
			log.Debug(fmt.Sprintf("[WORKER#%d] [%s] "+user.At()+"->"+m.To, id, m.Type()))
		} else {
			log.Error("Unknown user with signature " + m.Signature)
		}

	}
}
