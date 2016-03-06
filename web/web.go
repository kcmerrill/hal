package web

import (
	"fmt"
	log "github.com/Sirupsen/logrus"
	"github.com/kcmerrill/hal/message"
	"io/ioutil"
	"net/http"
)

func incoming(msgs chan *message.Message) func(w http.ResponseWriter, r *http.Request) {
	return func(w http.ResponseWriter, r *http.Request) {
		if incoming, err := ioutil.ReadAll(r.Body); err == nil {
			if m, open_error := message.Open(incoming); open_error == nil {
				/* Send in the message! */
				msgs <- m
			} else {
				log.Error(open_error.Error())
			}
		}
	}
}

func Boot(port int, msgs chan *message.Message) {
	http.HandleFunc("/", incoming(msgs))
	err := http.ListenAndServe(fmt.Sprintf(":%d", port), nil)
	if err != nil {
		/* Bummer .. we can't start our http server */
		log.Error(fmt.Sprintf("Unable to start http server on port %d", port))
	} else {
		log.Info(fmt.Sprintf("Listening to web requests on port %d", port))
	}
}
