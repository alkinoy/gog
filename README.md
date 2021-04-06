# Test task
## Exampel of coding ability of Oleskiy Tykhonovskyy

This project is a short answer for the standard code task I got on job interview.

## Requirements
### Common
- No frontend required
- Some overengineering required.

### Task description

REST API application should be created for the 2 main section: Product and Cart

#### Product API
The API should expose methods to:
- Add new product
- Remove product
- Update product's title or/and price
- List all products.

#### Restrictions
- Product title should be unique
- Should be at least 5 products in the list
- List should be paginated, max 3 products per page

#### Cart API
The API should expose methods to:
- create a cart
- add product into a cart
- remove product from a cart
- list all products in a cart
- return a sum of costs of all products in a cart

#### Restrictions
- Max up to 10 units of the same prouct in the cart
- max up to 3 different products in the cart

##Implimentation
You should have Composer, Docker and PHP8.0 to install project locally
### How to Install
- clone project
```
git clone git@github.com:alkinoy/gog.git
```
- go to the app directory
```
cd gog
```
- run composer
```
composer install
```
- run
```
sudo make dev
```
it will setup a set of docker containers: Mysq, Grafana, InfluxDb, etc. 
Make sure all services are up. It may require up to couple minutes to start MySql server in docker.
- run doctrine migration
```
./bin/console do:mi:mi
```
- load doctrine fixtures
```
./bin/console do:fi:lo
```
- starts local server. You should have binary symfony installed. See details [here](https://symfony.com/download).
```
symfony serve -d
```
- execute Behat tests (important. Should be run on newly created DB. Tests don't implement fixtures reload)
```
vendor/bin/behat
```
- execute unit tests
```
php bin/phpunit
```

I hope here will be all right.
[](https://i.imgur.com/cLrvUpK.png)

[](https://i.imgur.com/ESp0IYQ.png)

Open Swagger doc page and enjoy! Pay attention on Swagger Authorisation, use example UUID.
```
https://localhost:8000/api/doc
```

Also additional dashboard may be created in Grafana (http://localhost:3003), but unfortunatelly this is out of the scope of this task. But data stored into InfluxDb.
![](https://i.imgur.com/C5QOFEm.png)

Use 
```
sudo make down
```
to stop your docker containers

## What can be improved
- Add more validations on incoming data (negative/zero values for quantity and price), 
- Add event system whe product added or removed in/from cart (fe., with RabbitMQ message system)
- Add more metrics in statistics
- Change authorisation system
- Manage API version based on client metadata (now it done through URL parameter)
- Add cache

