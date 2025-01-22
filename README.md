# Laravel API Application

## Introduction
This is a Laravel-based API application. It includes various features such as routing, throttling, request handling, exception management, and testing.

## Requirements
- PHP
- Composer
- Docker

## Installation

1. Clone the repository:
    ```sh
    git clone git@github.com:sumvee/laravel-api-nyt.git
    cd laravel-api-nyt
    ```

2. Install PHP dependencies:
    ```sh
    composer install
    ```

3. Copy the example environment file and configure the environment variables:
    ```sh
    cp .env.example .env
    ```

4. Generate the application key:
    ```sh
    php artisan key:generate
    ```

## Running the Application

1. Start the development server using Laravel Sail:
    ```sh
    vendor/bin/sail up -d
    ```

2. The application will be available at `http://localhost`.
  ```shell
  #sample curl request to get best sellers from api end point
  curl "http://localhost/api/v1/nyt/best-sellers?author=Dia&isbn[]=9780399178573"

```

## Testing

Run the tests using PHPUnit:
```sh
vendor/bin/sail test
