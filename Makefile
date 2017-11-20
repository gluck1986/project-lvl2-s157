install:
	composer install
test:
	composer run-script phpunit
run:
	./bin/gendiff
halp:
	./bin/gendiff --help
lint:
	composer run-script phpcs -- --standard=PSR2 src bin
