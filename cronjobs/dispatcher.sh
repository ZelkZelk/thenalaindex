#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
APP="$DIR/../app"
cd $APP
Console/cake dispatcher
