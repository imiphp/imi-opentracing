#!/bin/bash

__DIR__=$(cd `dirname $0`; pwd)

cd $__DIR__/../

vendor/bin/imi-cli --app-namespace "Imi\OpenTracing" generate/facade "Imi\OpenTracing\Facade\Tracer" "Tracer" && \

vendor/bin/php-cs-fixer fix
