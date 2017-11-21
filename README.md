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

To use the services follow the documentation in your local running instance at http://127.0.0.1:8000/doc if you are using the symfony builtin server.

For example, to get the first 5 todos from the command line in a linux enviroment with curl:

* curl -i -H "Accept: application/json" -H "Content-Type: application/json" -X GET http://127.0.0.1:8000/todos

## Pagination and filtering

Pagination: To paginate just add ?page=n to the query string, where n is the number page you want. You can also use offset=n, to skip the n first results. Keep in mind that page has precedence if both are present.

Filter: Filter only works on the following Document fields:

completed=true|false

due_date:date(Y-m-d)

created_at:date(Y-m-d)

updated_at:date(Y-m-d)

To filter just add ?filter=name::value to the query string, where name is one of the fields listed and value is the value to filter. If you want to filter with more than one field just concatenate the name and value with "|" like this: name::value|name2::value.

All this was done following the "RESTful Best Practices" by http://www.restapitutorial.com/