# Task manager system using Clean Architecture, DDD and CQRS. [![CI](https://github.com/k0t9i/TaskManager-php-ddd/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/k0t9i/TaskManager-php-ddd/actions/workflows/ci.yml)

## Environment setup
1) Install Docker
2) Clone the project: `git clone https://github.com/k0t9i/TaskManager-php-ddd.git`
3) Run docker containers: `docker-compose -f ./backend/symfony/docker-compose.yml up -d --build`
4) Setup application: `make setup`. This step installs the composer dependency, generates JWT keys, runs migrations, warms up the cache and reloads the supervisor.
## Performing checks
- `make test` - phpunit tests
- `make code-style` - php-cs-fixer checks
- `make static-analysis` - psalm checks
- `make check-all` - all of the above
## Api
Swagger api is available on http://127.0.0.1:8081/api/doc
## Database
All data is stored on one server in one database, you can see the database structure via adminer http://127.0.0.1:9080/.
## Frontend example
Simple vue3 application is available on http://127.0.0.1:7080/
## Code structure
```scala
├── Projects  // Code related to a specific bounded context
│   ├── Application // Application layer depends only on Domain layer
│   │   ├── Command
│   │   ├── Handler // Command handlers
│   │   ├── Service // Application services
│   │   └── Subscriber // Domain event subscribers
│   ├── Domain // Domain logic layer does not depend on other layers
│   │   ├── Collection
│   │   ├── Entity // Entities and aggregate roots
│   │   ├── Event // Domain events
│   │   ├── Exception // Domain exceptions
│   │   ├── Repository // Repository interfaces
│   │   └── ValueObject
│   └── Infrastructure // Infrastructure layer depends on Domain and Application layers
│       ├── Persistence // Entity mapping in database
│       ├── Repository // Repository realizations
│       └── Service
└── Shared // Common code for all bounded contexts
```
## Buses
Command and query buses are synchronous, implemented via the symfony messenger.

The application has two event buses: the domain event bus and the integration event bus.

The domain event bus is synchronous, implemented via the symfony messenger and used inside bounded contexts.

The integration event bus is asynchronous, implemented via the symfony messenger and RabbitMQ (http://127.0.0.1:15673/). Events are received from RabbitMQ through the Symfony Messenger, which is launched using the supervisor.

## Projections
Projections are collected from domain events using the console command, which is launched using the supervisor.

You can always reload projections from scratch with `make projections-reload`