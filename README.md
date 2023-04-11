# ProAdvisor Drivers APIs
## System Requirements
1. PHP (Version 8.0+)
2. Composer
3. Git
4. Mysql (Version 8.0)
5. 1 GB Ram(at least)
6. Apache Webserver(Version 2.4)
7. Postman (for APIs)

## Framework Used
1. Laravel 8.65 for PHP

## Project Setup for local/development/staging
- Git clone the repository
- Run composer install to load PHP dependencies to root of project folder
```shell
composer install
```
-  generate secure key
```
php artisan key:generate
```
- Run passport:install to generate the secure passport access tokens
```
php artisan passport:install
```
- Create a .env file to the root of the project folder if not created by copying the .env.example file
- Setup the configuration of app environment as local, database connection and other settings in .env file.
   * set DB_HOST
   * set DB_DATABASE
   * set DB_USERNAME
   * set DB_PASSWORD
- Setup virtual host and point the document location to public folder
