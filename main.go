package main

import (
	log "github.com/Sirupsen/logrus"
	hal "github.com/kcmerrill/hal/hal"
	"github.com/kcmerrill/shutdown.go"
	"syscall"
)

func main() {

	/* Giddy up! */
	hal.Boot()

	/* Catch the shutdown */
	shutdown.WaitFor(syscall.SIGINT, syscall.SIGTERM)

	log.Info("Shutting down ...")
}
