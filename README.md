# wwwstaff
## Introduction
L'objectif du projet folenski/wwwstaff est de pouvoir fournir un petit framework afin de faciliter la production de site Web.
## Prerequis
* php8  => faire un phpinfo()
* composer
* python3 pour les tests reqman 

Installer composer [composer](https://getcomposer.org/download/)

## Installation php
```bash
mkdir wwwtests
cd wwwtests
git clone https://github.com/folenski/wwwstaff.git
composer install
composer dump-autoload 
```
---
## Installation du javascript
Etape facultative, nécessaire pour reconstruire le javascript de tests

```bash
npm install
```
## Creation de la bse
```bash
# création de la base 
./folenski/cli/create_base.php
# chargement des données minimum
./folenski/cli/load_json.php asset/data/template.json
./folenski/cli/load_json.php asset/data/environment.json
./folenski/cli/load_json.php asset/data/data.json
```
## Lancement site
```bash
php -S localhost:8080 -t public
```

---