#!/usr/bin/env bash
ARGS="$@"

if [ $# -eq 0 ]; then
	COMMAND="install"
	else
	COMMAND=$ARGS
	fi

docker run --rm --interactive --tty \
  --volume $PWD/htdocs:/app \
  --volume ${COMPOSER_HOME:-$HOME/.composer}:/tmp \
   -u $(id -u ${USER}):$(id -g ${USER}) \
  composer:2 composer $COMMAND
