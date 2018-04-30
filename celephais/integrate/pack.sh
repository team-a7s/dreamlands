#!/bin/bash

pushd () {
    command pushd "$@" > /dev/null
}

popd () {
    command popd "$@" > /dev/null
}

pushd $(git rev-parse --show-toplevel)
rm -f dreamlands.tar dreamlands.tar.gz
git archive --format=tar HEAD celephais -o dreamlands.tar

pushd kadath
composer install > pack.log
composer dump -o --apcu > pack.log
popd

pushd ulthar
yarn build > pack.log
popd

pushd underworld
yarn build > pack.log
popd

tar rf dreamlands.tar celephais/integrate/kadath.env kadath ulthar/dist underworld/build
gzip dreamlands.tar

popd

echo '{}'
