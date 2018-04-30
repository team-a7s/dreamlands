#!/bin/bash
PATH=$PATH:/opt/bin

function run_yarn {
  dir=$1
  shift
  docker run --rm -it -m=1g --oom-kill-disable -v $(realpath $dir):/workspace -v $(realpath ./ulthar.env):/workspace/.env kkarczmarczyk/node-yarn:8.0 yarn $@
}
function run_composer {
  dir=$1
  shift
  docker run --rm -it -m=1g --oom-kill-disable -v $(realpath $dir):/app composer:1.6 composer $@
}

pushd $(dirname $0)
# run_yarn ../../ulthar
# run_yarn ../../ulthar build
# run_yarn ../../underworld
# run_yarn ../../underworld build
# run_composer ../../kadath install -o --apcu-autoloader --ignore-platform-reqs

cp kadath.env ../../kadath/.env

pushd ..
docker-compose -f docker-compose.yml -f docker-compose.prod.yml pull
docker-compose -f docker-compose.yml -f docker-compose.prod.yml build
popd
popd
