package channel

import (
	"github.com/kcmerrill/hal/src/connection"
	"github.com/kcmerrill/hal/src/message"
	"github.com/kcmerrill/hal/src/users"
)

func Broadcast(m *message.Message) {
	for connection, _ := range connection.Connections() {
		if user, err := users.Fetch(connection.Signature); err == nil {
			/* User exists ... does it have this channel? */
			if user.HasChannel(m.To) {
				/* Oh snap! User has the channel .. Send them the message */
				connection.Write(m)
			}
		}
	}
}
