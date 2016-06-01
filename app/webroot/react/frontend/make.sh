#!/bin/bash
nodejs $(which browserify) global.js -t [ babelify --presets [react] ] > main.js
