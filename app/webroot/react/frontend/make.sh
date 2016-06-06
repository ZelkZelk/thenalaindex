#!/bin/bash
nodejs $(which browserify) frontend.js -t [ babelify --presets [react] ] > main.js
