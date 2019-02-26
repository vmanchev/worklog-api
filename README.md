# Worklog API
RESTful API for tracking work and time

## Configuration

Use the `/app/config/config.sample.php` file to create a configuration file for each 
environment. Replace `sample` with the desired environment name. The following file 
names are just an example of what is expected and are included in the .gitignore file. 
The environment name must be defined as Apache/Nginx `APPLICATION_ENV` variable. If 
different environment names are used, don't forget to update your .gitignore file in 
order to avoid commiting sensitive information in the repository.

```
config.development.php
config.staging.php
config.production.php
```

## Installation

1. Install project dependencies via composer:

```
composer install
```

2. Run migrations 

```
ToDo
```