REST TODO APP
===

A Symfony project created on November 17, 2017, 12:43 pm.
This is a simple TODO rest api for testing.

### Requirements

[Composer](https://getcomposer.org)

[MongoDB](https://www.mongodb.com/)

[php 5.6](https://www.php.net/)

[php-mongo extension](https://secure.php.net/manual/es/mongo.installation.php)

[memcached](http://memcached.org/)

[php-memcached extension]

### Installation

* git clone https://github.com/hernol/rest_todo_test.git
* cd rest_todo_test
* composer update
* ./bin/console server:run

### Usage

To use the services follow the documentation in your local running instance at http://localhost:8000/doc if you are using the symfony builtin server.

For example, to get the first 5 todos from the command line in a linux enviroment with curl:

* curl -i -H "Accept: application/json" -H "Content-Type: application/json" -X GET http://hostname