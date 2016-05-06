#!/bin/bash
nodejs $(which browserify) $1 -t [ babelify --presets [react] ] > $2
