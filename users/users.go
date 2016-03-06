package users

import (
	"errors"
)

var Registered map[string]*Info

func init() {
	Registered = make(map[string]*Info)
}

func Fetch(handle string) (*Info, error) {
	if string(handle[0]) == "@" {
		/* Lets look up a user based on their username */
		for _, user := range Registered {
			if user.At() == handle {
				return user, nil
			}
		}
		return nil, errors.New("The user " + handle + " does not exist")
	} else {
		if u, exists := Registered[handle]; exists {
			return u, nil
		} else {
			return nil, errors.New("Unable to find the signature " + handle)
		}
	}
}

type Info struct {
	Username  string
	Signature string
	meta      map[string]string
	Channels  map[string]int
}

func Register(u *Info) (*Info, error) {
	if _, exists := Registered[u.Signature]; !exists {
		Registered[u.Signature] = u
		return u, nil
	} else {
		return u, errors.New("Signature " + u.Signature + " already exists ...")
	}
}

func (u *Info) HasChannel(channel_name string) bool {
	if _, exists := u.Channels[channel_name]; exists {
		return true
	}
	return false
}

func (u *Info) JoinChannel(channel_name string, permission int) int {
	u.Channels[channel_name] = permission
	return u.Channels[channel_name]
}

func (u *Info) LeaveChannel(channel_name string) bool {
	delete(u.Channels, channel_name)
	return true
}

func (u *Info) At() string {
	return "@" + u.Username
}
