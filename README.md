## Interview project

Prerequisites : 
PHP 8.5 with the pdo_pgsql extension enabled
Composer
The Symfony CLI
Docker & Docker Compose

Steps to run the project :
- clone the project
- cd to the project
- run composer install to install the dependencies
- run `php bin/console sql-migrations:execute --drop-database` to create the database and run the migrations
- run `bin/console app:import:csv <path/to/your/csv/file.csv>` to import the data to the database
- run `symfony server:start` to start the web server
- run `bin/console messenger:consume -vvv` to start the messenger (the `-vvv` option is to see the messages being consumed in real time)
- assuming the web server started on `localhost:8000`, go to `localhost:8000/api/alerter` with the parameters `insee` and `key`. Example : `localhost:8000/api/alerter?insee=75056&key=YOUR_API_KEY`
- the app returns a 200 code if everything is ok
- the api key must be in your .env file
