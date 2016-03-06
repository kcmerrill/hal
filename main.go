package main

import (
	hal "github.com/kcmerrill/hal.go/hal"
	"github.com/kcmerrill/shutdown.go"
	log "github.com/kcmerrill/snitchin.go"
	"syscall"
)

func main() {
	/* Setup some basics ... */
	log.Channel("DEFAULT").SetLevel(0)
	log.CustomLevel("MESSAGE", 100, "\033[38;5;0m")

	/* Giddy up! */
	hal.Boot()

	/* Catch the shutdown */
	shutdown.WaitFor(syscall.SIGINT, syscall.SIGTERM)

	log.OK("Shutting down ...")
}
