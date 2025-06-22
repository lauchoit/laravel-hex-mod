<p align="center">
  <img src="src/assets/logo_lauchoit.jpg" alt="LauchoIT Logo" width="300">
</p>

# Laravel Hex Mod

> ⚙️ *A modular scaffolding generator for Laravel based on Hexagonal Architecture (Ports & Adapters).*

[![License](https://img.shields.io/github/license/LauchoIT/laravel-hex-mod.svg)](https://github.com/LauchoIT/laravel-hex-mod/blob/main/LICENSE)
[![Packagist](https://img.shields.io/packagist/v/lauchoit/laravel-hex-mod.svg)](https://packagist.org/packages/lauchoit/laravel-hex-mod)
[![Made by LauchoIT](https://img.shields.io/badge/Made%20by-LauchoIT-blue)](https://lauch...

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
#### With fields and type and optional Usage
```bash
php artisan make:hex-mod client --no-test
```

If you provide the `--no-test` option, the generator will skip creating tests for the module.

## 🙌 Made with ❤️ by [LauchoIT](https://lauchoit.com)

Feel free to contribute or suggest features via GitHub issues or PRs.

## 📜 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

```

## 🧪 Test Coverage

The generator produces unit and feature tests for:

- Entity classes
- NotFound exceptions
- UsesCases feature scenarios

Run your tests:

```bash
php artisan test
```