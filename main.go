package main

import (
	hal "github.com/kcmerrill/hal/src/hal"
	log "github.com/kcmerrill/snitchin.go"
)

func main() {
	/* Setup some basics ... */
	log.Channel("DEFAULT").SetLevel(0)
	log.CustomLevel("MESSAGE", 100, "\033[38;5;0m")

	/* Giddy up! */
	hal.Boot()
}
