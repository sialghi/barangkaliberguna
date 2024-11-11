# Website Layanan Fakultas Sains dan Teknologi

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>
<br>
<br>

This repository contains the source code for the [Website Layanan Fakultas Sains dan Teknologi](https://layanan-fst.uinjakarta.id/).

## Descriptions

This website developed to provide various services related to the Faculty of Science and Technology at our institution. It aims to streamline and enhance the administrative processes within the faculty.

## How to run

1. Clone the project into your local directory
    ```sh
    git clone git@github.com:Nocturned/Clone-Website-Pelayanan-Prodi-TI.git
    ```

2. Go into the cloned repositoy, make sure to go into the 'laravel' folder
    ```sh
    cd <project-repository>/laravel
    ```

3. Install all the dependencies
    ```sh
    composer install
    ```

4. Generate a .env file from .env.example
    ```sh
    copy .env.example .env
    ```

5. Generate a laravel application key inside of .env
    ```sh
    php artisan key:generate
    ```

6. Go to https://mailtrap.io/ 

 - Register or login to your account

 - Create a new *email testing*, then go into your inboxes

 - Go to inbox settings > integration

 - Click on SMTP, then on **Code Samples** click on ***PHP*** then choose ***Laravel 9+***

 - Click copy (make sure to click it and not to manually copy it), then paste it (replace) into your .env

7. Configure your database and .env file

8. Migrate the database
    ```sh
    php artisan migrate
    ```

9. (optional) Fill the database tables with dummy data
    ```sh
    php artisan db:seed
    ```

10. Run the application
    ```sh
    php artisan serve
    ```

## Development Tools

- [WampServer 3.3.0](https://sourceforge.net/projects/wampserver/)
- PHP 8.2 (include in WampServer)
- [PHP Composer](https://getcomposer.org/download/)

## Important Library

- Laravel 10
- [AdminLTE 3.13](https://github.com/jeroennoten/Laravel-AdminLTE/wiki)
- [Bootstrap 5.2](https://getbootstrap.com/docs/5.2/getting-started/introduction/)
- [Fontawesome 5.15.4](https://fontawesome.com/v5/search?o=r&m=free)

# Notes

## Accounts
```
password: @Password123
```

```
role: Dekan FST, Dosen TI
email: dekanfst@uinjkt.ac.id
```
```
role: Dekan Ushuluddin, Dosen Studi Agama-Agama
email: dekanushuluddin@uinjkt.ac.id
```
```
role: Kaprodi TI, Dosen TI
email: kaproditi@uinjkt.ac.id
```
```
role: Kaprodi Fisika, Dosen Fisika
email: kaprodifisika@uinjkt.ac.id
```
```
role: Kaprodi Tambang, Dosen TI
email: kaproditambang@uinjkt.ac.id
```
```
role: Kaprodi Hadis, Dosen Hadis
email: kaprodihadis@uinjkt.ac.id
```
```
role: Kaprodi Tasawuf, Dosen Tasawuf
email: kaproditasawuf@uinjkt.ac.id
```
```
(Memiliki 3 akun, tanda kurung gunakan angka 1, 2, 3)
role: Dosen TI
email: dosenti(1-3)@uinjkt.ac.id
```
```
(Memiliki 3 akun, tanda kurung gunakan angka 1, 2, 3)
role: Dosen Tambang
email: dosentambang(1-3)@uinjkt.ac.id
```
```
(Memiliki 3 akun, tanda kurung gunakan angka 1, 2, 3)
role: Mahasiswa TI
email: mahasiswati(1-3)@uinjkt.ac.id
```
```
(Memiliki 3 akun, tanda kurung gunakan angka 1, 2, 3)
role: Mahasiswa Tambang
email: mahasiswatambang(1-3)@uinjkt.ac.id
```

## Sidebar Menus

- app
  - config
    - adminlte.php

### Sidebar Menus Role Based Authentication

- app
  - Providers
    - AuthServiceProvider.php