package command

import (
	"github.com/kcmerrill/hal/src/message"
	"github.com/kcmerrill/hal/src/users"
	log "github.com/kcmerrill/snitchin.go"
	"strings"
)

type command struct {
	cmd      string
	cmd_raw  string
	modifier string
	param1   string
	executor string
}

func Execute(m *message.Message) {
	c := &command{
		cmd_raw:  m.Msg,
		executor: m.Signature,
	}

	c.Execute()
}

func (c *command) Execute() {
	cmd_split := strings.Split(string(c.cmd_raw[1:]), " ")
	for index, word := range cmd_split {
		switch index {
		case 0:
			c.cmd = word
		case 1:
			c.modifier = word
		case 2:
			c.param1 = word
		}
	}

	switch c.cmd {
	default:
		log.ERROR("Unknown command " + c.cmd)
	case "leave":
		if user, err := users.Fetch(c.executor); err == nil {
			user.LeaveChannel(c.modifier)
			log.INFO(user.At() + " left " + c.modifier)
		} else {
			log.ERROR("Unknown user " + c.executor)
		}
	case "join":
		if user, err := users.Fetch(c.executor); err == nil {
			user.JoinChannel(c.modifier, 1)
			log.INFO(user.At() + " joined " + c.modifier)
		} else {
			log.ERROR("Unknown user " + c.executor)
		}
	}
}
