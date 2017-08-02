#!/bin/bash

if [ -z $1 ]; then
    echo "usage: $0 <version>"
    exit 1
fi

PWD=$(cd $(dirname ${BASH_SOURCE[0]}) && pwd)
ln -fns $1 $PWD/current_release
