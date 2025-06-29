<p align="center">
  <img src="src/assets/logo_lauchoit.jpg" alt="LauchoIT Logo" width="150">
</p>

# Laravel Hex Mod

> ⚙️ *A modular scaffolding generator for Laravel based on Hexagonal Architecture (Ports & Adapters).*

[![License](https://img.shields.io/github/license/LauchoIT/laravel-hex-mod.svg)](https://github.com/LauchoIT/laravel-hex-mod/blob/main/LICENSE)
[![Packagist](https://img.shields.io/packagist/v/lauchoit/laravel-hex-mod.svg)](https://packagist.org/packages/lauchoit/laravel-hex-mod)
[![Made by LauchoIT](https://img.shields.io/badge/Made%20by-LauchoIT-blue)](https://lauchoit.com)

---

## 📦 Installation

You can install the package via Composer:

```bash
composer require lauchoit/laravel-hex-mod --dev
```

## 🚀 What It Does

This package generates a modular Laravel structure based on **Hexagonal Architecture**, automating the creation of:

- ✅ Domain Entities & EntitySource (Model of Laravel)
- ✅ Application UseCases
- ✅ Interface Adapters (Controllers, Repositories, Requests, Resources)
- ✅ Eloquent Models, Migrations, and Factories
- ✅ Automatic Unit & Feature Tests
- ✅ Route injection & AppServiceProvider bindings
- ✅ Shared folder

## 🚀 What It Does

This package scaffolds a complete Laravel module following **Hexagonal Architecture**, including:

```bash
src
├── Client
│   ├── Application
│   │   └── UseCases
│   │       ├── CreateClientUseCase.php
│   │       ├── DeleteByIdClientUseCase.php
│   │       ├── FindAllClientUseCase.php
│   │       ├── FindByIdClientUseCase.php
│   │       └── UpdateByIdClientUseCase.php
│   ├── Domain
│   │   ├── Entity
│   │   │   ├── Client.php
│   │   │   └── ClientSource.php
│   │   ├── Exceptions
│   │   │   └── ClientNotFoundException.php
│   │   ├── Mappers
│   │   │   └── ClientMapper.php
│   │   └── Repository
│   │       └── ClientRepository.php
│   ├── Infrastructure
│   │   ├── Controllers
│   │   │   └── ClientController.php
│   │   ├── Model
│   │   │   └── Client.php
│   │   ├── Repository
│   │   │   ├── ClientRepositoryImpl.php
│   │   │   └── UseCases
│   │   │       ├── CreateClientUseCaseImpl.php
│   │   │       ├── DeleteByIdClientUseCaseImpl.php
│   │   │       ├── FindAllClientUseCaseImpl.php
│   │   │       ├── FindByIdClientUseCaseImpl.php
│   │   │       └── UpdateByIdClientUseCaseImpl.php
│   │   ├── Requests
│   │   │   ├── CreateClientRequest.php
│   │   │   └── UpdateClientRequest.php
│   │   ├── Resources
│   │   │   └── ClientResource.php
│   │   └── Routes
│   │       └── ClientRoutes.php
│   └── Tests
│       ├── Feature
│       │   ├── CreateClientTest.php
│       │   ├── DeleteByIdClientTest.php
│       │   ├── FindAllClientTest.php
│       │   ├── FindByIdClientTest.php
│       │   └── UpdateByIdClientTest.php
│       └── Unit
│           └── Domain
│               ├── Entity
│               │   ├── ClientSourceTest.php
│               │   └── ClientTest.php
│               └── Exceptions
│                   └── ClientNotFoundExceptionTest.php
└── Shared
    └── Responses
        ├── ApiResponse.php
        └── ValidationResponse.php
```

### 🧱 Usage

You can run the command in different ways depending on your needs:
#### Basic Usage
```bash
php artisan make:hex-mod client
```
This command will generate a complete module for `Client` with default fields `attribute1 and attribute2` all string.

#### With fields Usage
```bash
php artisan make:hex-mod client -f name,lastname,direction,description
```
This command will generate a complete module for `Client` with specific field, if your not provider type, the system makes field with a string type.

#### With fields and type Usage
```bash
php artisan make:hex-mod client -f name,lastname,direction,year:integer,registred_at:date,data:json,metadata:json
```
The type available are: `string`, `integer`, `float`, `boolean`, `date`, `datetime`, `json`, `longText`.

This command will generate a complete module for `Client` with specific field and type, if your not provider types, the system makes field with a string type.
#### Without Tests
```bash
php artisan make:hex-mod client --skip-test
```
If you provide the `--skip-test` option, the generator will skip creating tests for the module.
## Route
The generator automatically creates a route file for the module, which you can find in `src/Client/Infrastructure/Routes/ClientRoutes.php`. 


* You can customize the routes as needed, and they will be automatically loaded by the application.
* This route file is register automatically in the `bootstrap/app.php` of your Laravel application.
* You can see the routes in the `php artisan route:list` command.


## 🧪 Test Coverage
Note: you need to have the `phpunit` package installed and configured in your Laravel project to run the tests. You need run migratrions before runnig the tests.
The generator produces unit and feature tests for:

- Entity classes
- NotFound exceptions
- UsesCases feature scenarios

Run your tests:

```bash
php artisan test
```

## 🙌 Made with ❤️ by [LauchoIT](https://lauchoit.com)

Feel free to contribute or suggest features via GitHub issues or PRs.

## 📜 License

This package is open-sourced software licensed under the [MIT license](LICENSE).
