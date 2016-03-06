package command

import (
	log "github.com/Sirupsen/logrus"
	"github.com/kcmerrill/hal/message"
	"github.com/kcmerrill/hal/users"
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
		log.Error("Unknown command " + c.cmd)
	case "register":
		u := &users.Info{
			Username:  c.modifier,
			Signature: c.param1,
		}
		if user, err := users.Register(u); err == nil {
			log.Info(user.At() + " registered")
		} else {
			log.Error(err.Error())
		}
	case "leave":
		if user, err := users.Fetch(c.executor); err == nil {
			user.LeaveChannel(c.modifier)
			log.Info(user.At() + " left " + c.modifier)
		} else {
			log.Error("Unknown user " + c.executor)
		}
	case "join":
		if user, err := users.Fetch(c.executor); err == nil {
			user.JoinChannel(c.modifier, 1)
			log.Info(user.At() + " joined " + c.modifier)
		} else {
			log.Error("Unknown user " + c.executor)
		}
	}
}
