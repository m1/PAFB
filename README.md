# PHP API Framework Benchmark (PAFB)

PAFB is a benchmark tool you can install yourself to test popular frameworks to build APIs. In this README.md you will also find results tested on my own server but you're encouraged to install it and test it yourself.

It currently tests [Phalcon](https://phalconphp.com/en/), [Lumen 5.2](https://lumen.laravel.com/), [Silex 1.3](http://silex.sensiolabs.org/), [Slim 3](http://www.slimframework.com/) and supports the following databases; MySQL, Postgresql and MariaDB.

## Requirements

* A `MySQL`, `MariaDB` or `Postgresql` database. 
* `Python` installed. 
* `Nginx` installed. 
* `Composer` installed. 
* `ApacheBench` installed.

## Setup

To test `Phalcon`, you'll need to install this first, you can find more info [here](https://phalconphp.com/en/download), you'll also need `Composer` installed globally or in the root (`pafb`) folder, you can find details on how to install [here](https://getcomposer.org/download/).

Run `sudo ./install.py` to install. The available arguments for `install.py` are as follows:

`-c {mysql,pgsql,mariadb}, --connection={mysql,pgsql,mariadb}`
What database engine to use

`-H HOST, --host HOST`
Database host

`-u USERNAME, --username USERNAME`
Database username

`-P PORT, --port PORT`
Database port
 
`-p PASSWORD, --password PASSWORD`
Database password

The installer creates the nginx configs for each of the frameworks, but you need to edit your `/etc/hosts` to add `127.0.0.1 pafb.dev`

The installer then `dumpautoload`s for each of the frameworks and then you're ready to test!

## Usage

To test run `./test.py`, the available arguments for `test.py` are:

`-f {all,phalcon,slim,silex,lumen} [{all,phalcon,slim,silex,lumen} ...], --frameworks {all,phalcon,slim,silex,lumen} [{all,phalcon,slim,silex,lumen} ...]`
Which frameworks to test

`-n NUMBER, --number NUMBER`
Number of requests to make

`-c CONCURRENT, --concurrent CONCURRENT`
Number of multiple requests to make at a time

After running the tests, you should get an output something like this:

````
Welcome to the PHP API Framework benchmark (PAFB)

Testing: Insert

Results:
1st. Phalcon - 1050.44 requests per second
2nd. Slim - 351.24 requests per second
3rd. Silex - 306.56 requests per second
4th. Lumen - 217.24 requests per second

Testing: Update

Results:
1st. Phalcon - 1034.06 requests per second
2nd. Slim - 368.85 requests per second
3rd. Silex - 345.57 requests per second
4th. Lumen - 265.17 requests per second

Testing: Select

Results:
1st. Phalcon - 1177.9 requests per second
2nd. Slim - 396.05 requests per second
3rd. Silex - 352.17 requests per second
4th. Lumen - 222.13 requests per second

Testing: Delete

Results:
1st. Phalcon - 1213.0 requests per second
2nd. Slim - 402.74 requests per second
3rd. Silex - 353.32 requests per second
4th. Lumen - 277.09 requests per second

Testing: Index

Results:
1st. Phalcon - 46.15 requests per second
2nd. Lumen - 45.14 requests per second
3rd. Silex - 43.19 requests per second
4th. Slim - 41.73 requests per second

Overall Results
1st. Phalcon - 4521.55 total rps
2nd. Slim - 1560.61 total rps
3rd. Silex - 1400.81 total rps
4th. Lumen - 1026.77 total rps
````

## My results

These results are using my desktop which runs `Ubuntu 15.10`, `MySQL 5.6.28`, `Postgresql 9.4.6`, `PHP 5.6.11`

### MySQL

````
Welcome to the PHP API Framework benchmark (PAFB)

Testing: Insert

Results:
1st. Phalcon - 1050.44 requests per second
2nd. Slim - 351.24 requests per second
3rd. Silex - 306.56 requests per second
4th. Lumen - 217.24 requests per second

Testing: Update

Results:
1st. Phalcon - 1034.06 requests per second
2nd. Slim - 368.85 requests per second
3rd. Silex - 345.57 requests per second
4th. Lumen - 265.17 requests per second

Testing: Select

Results:
1st. Phalcon - 1177.9 requests per second
2nd. Slim - 396.05 requests per second
3rd. Silex - 352.17 requests per second
4th. Lumen - 222.13 requests per second

Testing: Delete

Results:
1st. Phalcon - 1213.0 requests per second
2nd. Slim - 402.74 requests per second
3rd. Silex - 353.32 requests per second
4th. Lumen - 277.09 requests per second

Testing: Index

Results:
1st. Phalcon - 46.15 requests per second
2nd. Lumen - 45.14 requests per second
3rd. Silex - 43.19 requests per second
4th. Slim - 41.73 requests per second

Overall Results
1st. Phalcon - 4521.55 total rps
2nd. Slim - 1560.61 total rps
3rd. Silex - 1400.81 total rps
4th. Lumen - 1026.77 total rps
````

### Postgresql

````
Welcome to the PHP API Framework benchmark (PAFB)

Testing: Insert

Results:
1st. Phalcon - 220.55 requests per second
2nd. Slim - 153.93 requests per second
3rd. Silex - 140.58 requests per second
4th. Lumen - 121.87 requests per second

Testing: Update

Results:
1st. Phalcon - 207.4 requests per second
2nd. Slim - 148.06 requests per second
3rd. Silex - 142.28 requests per second
4th. Lumen - 127.26 requests per second

Testing: Select

Results:
1st. Phalcon - 214.22 requests per second
2nd. Slim - 163.64 requests per second
3rd. Silex - 153.01 requests per second
4th. Lumen - 121.29 requests per second

Testing: Delete

Results:
1st. Phalcon - 208.95 requests per second
2nd. Slim - 153.68 requests per second
3rd. Silex - 145.76 requests per second
4th. Lumen - 132.52 requests per second

Testing: Index

Results:
1st. Phalcon - 88.99 requests per second
2nd. Silex - 87.41 requests per second
3rd. Slim - 81.54 requests per second
4th. Lumen - 76.6 requests per second

Overall Results
1st. Phalcon - 940.11 total rps
2nd. Slim - 700.85 total rps
3rd. Silex - 669.04 total rps
4th. Lumen - 579.54 total rps
````

