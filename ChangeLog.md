# Changelog

Projet commencé en 2020 afin de faciliter la production d'un site WEB sur un hebergement mutualisé (php)

## [Unreleased]

### Added
- Use Swagger to provide information about the REST API : message.
- Add new properties to the option object (table environment), see our wiki for more details.
- Refactoring code to Www class and Render Class
- Add update fonctionality 

### Fixed

## [1.1.0] - 2023-07-10

### Added

- Ajout d'un filtre contre le spam
- Ajout d'une nouvelle table back_list afin de stocker les règles spam
- Suppression du champs j_contact dans la table environment
- Suppression des warning php 8.2, syntaxes dépreciées ... 

### Fixed

- Remove the foreign key on data table, this key linked tables data and template
- Method put in table class, bug about a merge of arrays 
- Script load_json.php, add an error if the file don't exist 

## [1.1.0] - 2022-12-21

### Added

- Class Drivers/Sqlite
- Utilisation des tags sur les class : Data, Environment, Log, Message, Template, Token, Log
- Renommage du champs Token/revoke en Token/revoked, revoke est un mot clé sur MYSQL
- Url pour l'installation de la base et chargement des données http://localhost:8080/start

### Fixed

- Fixed cli/create_base.php pour prise en compte le drivers sqlite
- Fixed sql limit  
 
## [1.0.0] - 2022-12-13

### Added

Version initiale


