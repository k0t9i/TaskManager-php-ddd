# Task manager system using Clean Architecture, DDD and CQRS. [![CI](https://github.com/k0t9i/TaskManager-php-ddd/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/k0t9i/TaskManager-php-ddd/actions/workflows/ci.yml)

## Environment setup
1) Install Docker
2) Clone the project: `git clone https://github.com/k0t9i/TaskManager-php-ddd.git`
3) Run docker containers: `docker-compose -f ./symfony/docker-compose.yml up -d --build`
4) Setup application: `make setup`. This step installs the composer dependency, generates JWT keys, runs migrations, warms up the cache and reloads the supervisor.
## Performing checks
- `make test` - phpunit tests
- `make code-style` - php-cs-fixer checks
- `make static-analysis` - psalm checks
- `make check-all` - all of the above