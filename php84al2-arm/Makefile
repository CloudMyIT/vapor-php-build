SHELL := /bin/bash

compiler: compiler.Dockerfile
	docker build -f ${PWD}/compiler.Dockerfile -t vapor/runtime/compiler:latest .

build: compiler
	docker build -f ${PWD}/php.Dockerfile -t vapor/runtime/php-84al2-arm:latest $(shell helpers/buildArguments.php php84) .

distribution: build
	docker run --rm \
		--env PHP_SHORT_VERSION=84al2-arm \
		--volume ${PWD}/runtime:/runtime:ro \
		--volume ${PWD}/../export:/export \
		--volume ${PWD}/export.sh:/export.sh:ro \
		vapor/runtime/php-84al2-arm:latest \
		/export.sh
