.PHONY: *

all: docker synchronization composer-update tests

clean:
	_USER=$$USER && \
	sudo chown $$_USER:$$_USER . -R

add-package:
	read -p "Enter package context: " context; \
	read -p "Enter package name: " name; \
	[ "$$context" != "" ] && [ "$$name" != "" ] && \
	git subtree add --prefix=packages/$$context/$$name git://github.com/petrknap/`echo $$context | tr A-Z a-z`-`echo $$name | tr A-Z a-z`.git master

docker:
	mkdir temp/docker || true
	cp php.dockerfile temp/docker/php.dockerfile
	sudo docker build -f temp/docker/php.dockerfile -t petrknap/php temp/docker

docker-run-php:
	sudo docker run -v $$(pwd):/app -v $$(pwd):/mnt/read-only/app:ro --rm petrknap/php bash -c "cd /app && ${ARGS}"
	make clean

composer:
	make docker-run-php ARGS="composer ${ARGS}"

composer-install:
	make composer ARGS="install"

composer-update:
	make composer ARGS="update"

synchronization:
	sudo docker run -v $$(pwd):/app --rm php:7.0-cli bash -c "cd /app && ./bin/synchronize.php"
	make clean

tests: composer-install
	make docker-run-php ARGS="vendor/bin/phpunit ${ARGS}"

tests-on-packages:
	rsync -r --delete --exclude=composer.lock --exclude=vendor packages/ temp/packages/;
	for package in temp/packages/Php/*; do \
		make docker-run ARGS="cd $${package} && composer update && vendor/bin/phpunit ${ARGS}"; \
	done

publish: publish-web tests
	git subsplit init https://github.com/petrknap/php
	git subsplit publish --heads=master --update "packages/Php/Enum:git@github.com:petrknap/php-enum.git packages/Php/FileStorage:git@github.com:petrknap/php-filestorage.git packages/Php/Profiler:git@github.com:petrknap/php-profiler.git packages/Php/ServiceManager:git@github.com:petrknap/php-servicemanager.git packages/Php/Singleton:git@github.com:petrknap/php-singleton.git" #generated php
	rm -rf .subsplit

publish-web:
	git subsplit init https://github.com/petrknap/php
	git subsplit publish --heads=master --update "projects/petrknap.github.io:git@github.com:petrknap/petrknap.github.io.git"
	rm -rf .subsplit
