#!/bin/bash
nodejs $(which browserify) email.js -t [ babelify --presets [react] ] > main.js
