## MOARC

MOARC is a Lightweight MOFH Web Hosting and Let's Encrypt and GoGetSSL SSL Management library written in php specially for small hosting platforms.

![AppVeyor](https://img.shields.io/badge/Licence-MPL-lightgreen)
![AppVeyor](https://img.shields.io/badge/Version-0.1_alpha-lightgrey)
![AppVeyor](https://img.shields.io/badge/Build-passing-lightgreen)
![AppVeyor](https://img.shields.io/badge/PHP-7.x-lightgrey)
![AppVeyor](https://img.shields.io/badge/MySQL-5.2-lightgrey)
![AppVeyor](https://img.shields.io/badge/Type-Library-lightgrey)
![AppVeyor](https://img.shields.io/badge/forked-MOFHY_Lite-lightgrey)

## Table of Content 

- [Features](#features)
- [Requirements](#requirements) 
- [Installation](#installation)
- [Dependencies](#dependencies)
- [Documentation](#documentation)
- [Contributer](#contributer)
- [Copyright](#copyright)

## Features

Some of the features are listed below:
- Registration and Login system. 
- User profile management system.
- Password and Secret key reset system.
- MyOwnFreeHost accounts management system.
- <s>Let's Encrypt SSL management system. </s>
- <s>GoGetSSL SSL management system. </s>
- User support management system.
- Easy to use template system. 
- All-In-One standalone library.

## Requirements

Minimum requirements to use MOARC are given below:
- PHP ^7.x (PHP 8.x not supported.)
- cURL ^1.x
- MySQL ^5.x
- openSSL ^1.x

## Installation

MOARC is prtty easy to install by just following some simple steps:
- Run following command on terminal inorder to install moarc and its dependencies.
``` composer require mahtab2003/moarc ``` 
- Change parameters in ```src/Client.php``` and ```config.php``` files.
- Setup database by importing ```db/table.sql``` file.
- That's it.

## Dependencies

The following libraries are required to run MOARC:
- PHPMailer ^5.2
- MOFH-Client ^0.7.1
  - GuzzleHTTP
  - Promises
  - PSR-4
- <s>ACME2 ^1.0</s>

## Documentation

A full guide of `how to use moarc` can be found on our [docs](DOCS.md).

## Contributer
This library is created, modified and maintained by [NXTS Developers](https://github.com/NXTS-Developers).

## Copyright
©️ Copyright 2022 NXTS Developer. Code released under the MPL License.
