# Демка по управлению группами и пользователями
Сценарии можно посмотреть [здесь](features/main.feature).

Приложение построено на базе SF 4.4 с использованием Domain Driven Design принципов.

Выделена доменная часть: [сущности](src/Domain/Entity), [write-команды](src/Domain/Command), [read-запросы](src/Domain/Query). Валидация описана частично в аннотациях к сущностям, частично - в [отдельном неймспейсе](src/Domain/Validation). С внешним миром домен взаимодействует через [веб-морду](src/EntryPoints/Http).

## Запуск приложения в dev и test-окружении
1. Вам понадобится Postgres-база. Проще всего запустить докер-контейнер с Постгресом с помощью команды `./env-up.sh` (ознакомьтесь с [docker-compose.dev.yml](docker-compose.dev.yml) перед запуском). Либо вы можете использовать Постгрес в вашей системе. При конфликте с портом создайте файл `.env.local` и пропишите в нём DSN (смотрите [.env](.env) для примера).
1. У вас должны быть настроен PHP версии 7.4.
1. Выполните `composer install`
1. Выполните `bin/console doctrine:migrations:migrate`
1. Выполните `bin/console server:run`
1. Откройте в браузере [http://localhost:8000/api/doc](http://localhost:8000/api/doc).

## Запуск тестов
`./test.sh`, при поднятой базе.

Тесты включают в себя проверку корректности swagger-документации и Behat-тесты.

## Остановка и удаление
1. `./env-down.sh`
1. Папку с приложением можно просто удалить.
