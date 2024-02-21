# Commercial Mortgage

#### Backend for project Commercial Mortgage built with Laravel, serving the clients through GraphQL API.

## Install and run

1. Clone the repository
2. Run `lando rebuild -y` in order to build composer packages
3. Run database migrations using command `lando artisan migrate`
4. Generate App Key using `lando artisan key:generate`
5. Generate Laravel Passport Key using `lando artisan passport:install`
6. Update the `PASSPORT_CLIENT_SECRET` in the .env file with the second generated passport secret in the terminal.
7. Optionally, run database seeders to initialize necessary database records
    1. `lando artisan db:seed --class=Termsheets`
    2. `lando artisan db:seed --class=ChangeTermsheetValueSeeder`
    3. `lando artisan db:seed --class=AssetTypesTableSeeder`
    4. `lando artisan db:seed --class=UsersTableSeeder`
8. Clear any cache using `lando artisan cache:clear`
9. Clear any config using `lando artisan config:clear`
10. Start the server with `lando start`

Note: in case lando does not start properly (happens on Windows machines), open `lando.yml` file and comment out the following lines:
```build_as_root:
- apt-get update -y
- apt-get install cron supervisor -y
- cp -f /app/resources/config/laravel-worker.conf /etc/supervisor/conf.d/
- cp -f /app/.lando/docker-php-entrypoint.sh /usr/local/bin/docker-php-entrypoint
```
Server host name will be [http://cm.lndo.site](http://cm.lndo.site)

Database URL (PostgreSQL) will be [http://db-cm.lndo.site]( http://db-cm.lndo.site)

## Coding Standards

This project is following `PSR12` standard. If you want to execute the review of the coding standard in your end you can use:

```
composer run pint
```

We are using a tool to review the coding standards with git hooks defined in the composer file you should execute:

```
./vendor/bin/cghooks add
```

If someone or yourself update the hooks please execute:

```
./vendor/bin/cghooks update
```

## Dev server
Dev server is being hosted on DigitalOcean, and CI/CD is set via Kubernetes.

[http://159.203.150.213/](http://159.203.150.213/)
