## MOARC

MOARC is a Lightweight MOFH Web Hosting and Let's Encrypt SSL Management library written in php specially for small hosting platforms.

![AppVeyor](https://img.shields.io/badge/Licence-MPL-lightgrey)
![AppVeyor](https://img.shields.io/badge/Version-0.1-lightgrey)
![AppVeyor](https://img.shields.io/badge/Build-passing-lightgreen)
![AppVeyor](https://img.shields.io/badge/PHP-7.x-lightgrey)
![AppVeyor](https://img.shields.io/badge/MySQL-5.2-lightgrey)
![AppVeyor](https://img.shields.io/badge/Type-Library-lightgrey)
![AppVeyor](https://img.shields.io/badge/forked-MOFHY_Lite-lightgrey)
![GitHub all releases](https://img.shields.io/github/downloads/NXTS-Developers/MOARC/total?style=plastic)

## Table of Content 

- [Features](#features)
- [Requirements](#requirements) 
- [Installation](#installation)
- [Documentation](#documentation)
- [Dependencies](#dependencies)
- [Contributer](#contributer)
- [Copyright](#copyright)

## Features

Some of the features are listed below:
- Registration and Login system. 
- User profile management system.
- Password and Secret key reset system.
- <s>MyOwnFreeHost accounts management system.</s>
- <s>Let's Encrypt SSL management system. </s>
- <s>User support management system.</s>
- Easy to use template system. 
- <s>All-In-One standalone library.</s>

## Requirements

Minimum requirements to use MOARC are given below:
- PHP ^5.x < ^7.x (PHP 8.x not supported yet.)
- <s>cURL ^1.x</s>
- MySQL ^5.x
- <s>openSSL ^1.x</s>

## Installation

MOARC is prtty easy to install by just following some simple steps:
- Download MOARC from our github repoistery.
- Extract zip file.
- Run command ``` php composer update ``` on terminal inorder to install dependencies
- Change parameters in ```core.php``` and ```config.php``` files.
- Setup database by importing ```table.sql``` file.
- That's it.

## Documentation 

Documentation of using all functions and objects can be found on our [wiki](https://github.com/NXTS-Developers/MOARC/wiki/).

## Dependencies

The following libraries are required to run MOARC:
- PHPMailer ^5.2
- <s>MOFH-Client ^0.7.1</s>
  - <s>GuzzleHTTP</s>
  - <s>Promises</s>
  - <s>PSR-4</s>
- <s>LEClient ^1.3.0</s>

## Contributer
This library is created, modified and maintained by [NXTS Developers](https://github.com/NXTS-Developers).

## Copyright
©️ Copyright 2022 NXTS Developer. Code released under the MPL License.
