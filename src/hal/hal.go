package hal

import (
	"fmt"
	"github.com/kcmerrill/hal/src/channel"
	"github.com/kcmerrill/hal/src/command"
	"github.com/kcmerrill/hal/src/message"
	"github.com/kcmerrill/hal/src/socket"
	"github.com/kcmerrill/hal/src/users"
	"github.com/kcmerrill/hal/src/web"
	log "github.com/kcmerrill/snitchin.go"
)

func Boot() {
	log.INFO("Good Morning Dave ...")

	/* TODO: Remove me */
	users.Register(&users.Info{
		Username:  "dave",
		Signature: "bingowashisnameo",
		Channels: map[string]int{
			"#hal-demo": 1,
		},
	})

	/* Create a few channels */
	msgs := make(chan *message.Message)

	/* Get our message workers up and running */
	for x := 1; x <= 1000; x++ {
		go MessageWorker(x, msgs)
	}

	/* Start Hal's webserver in the background */
	go web.Boot(45000, msgs)

	/* Start Hal's websocket server */
	socket.Boot(45001, msgs)
}

func MessageWorker(id int, msgs chan *message.Message) {
	for {
		/* Grab a message off the channel */
		m := <-msgs

		if user, err := users.Fetch(m.Signature); err == nil {
			/* Quick logging */
			log.Write("MESSAGE", fmt.Sprintf("[WORKER#%d] "+user.At()+"->"+m.To, id))
			m.From = user.At()

			/* Depending on the type of message, do something with it */
			switch m.Type() {
			case "channel":
				channel.Broadcast(m)
			case "command":
				command.Execute(m)
			case "direct":
				//users.Message(m)
				log.DEBUG("A direct message to " + m.To)
			default:
				log.ERROR("Unknown message type: " + m.Type())
				continue
			}
		} else {
			log.ERROR("Unknown user with signature " + m.Signature)
		}

	}
}
