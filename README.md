# Introduction
> L'objectif du projet est de pouvoir créer un #framework permettant de fabriquer rapidement des sites WEB vitrines. Des outils open source existent déjà comme [Laravel](https://laravel.com), [Symfony](https://symfony.com/doc/current/index.html) mais l'objectif est de permettre de m'améliorer dans le développement.

# Prerequis
Vérifier que les composants suivants ont bien été installées :
* __php 8.1__ 
* Les modules optionnels php comme __sqlite3__, __mysql__
* __composer__, gestionnaire de projet pour php  ↗ [[Installation de Composer pour php]]

# initialisation de wwwstaff
Dans un premier temps, soit on récupère le projet dans son intégralité, soit les répertoires importants  ↗ [[Dépôts, branches GIT#Cloner un dépôt existant]].
## Clonage du projet
La récupération du projet permet d'avoir un environnement fonctionnel. 
💢 __Il sera nécessaire du publier le composant staff__
```bash
% git clone https://github.com/folenski/wwwstaff.git
...
```
## Création des répertoires
Création des répertoires suivants : app (javascript), tmp (temporaire php), sqldb (sqlite3)
`mkdir app tmp sqldb`

## Création du projet php, composer.json

```json
{
    "name": "mario/wwwtest",
    "autoload": {
        "psr-4": {
            "Staff\\": "folenski/staff/"
        }
    },
    "authors": [
        {
            "name": "folenski",
            "email": "folenskidev@nospan.com"
        }
    ],
    "require": {}
}
```
il faire les commandes : 
`composer require altorouter/altorouter`
`composer requite zordius/lightncandy`
et pour finir 
`composer dump-autoload`

# Paramétrage
Les fichiers de configuration pour la base de données ↗ [[Configuration SGBD Staff]]
il faut déclarer au minimum une session DEV
📃 backend/config/config.ini
il faut déclarer un fichier pdo pour sqlite3 ou mysql
📃 backend/config/pdosqlite.ini

# Initialisation des données du site
Tout d'abord, il faut alimenter la table "environment", ensuite les données et enfin les template
Le moteur de template n'est utile que si c'est le site qui génère les pages html.
Il est nécessaire de placer dans le répertoire views, le fichier qui devra effectuer le rendu final

# Premier lancement
Une fois que les fichiers de chargement ont été contrôlés, il suffit de lancer php
## lancement de php
`php -S localhost:8080 -t public`
avec chrome aller sur l'url http://localhost:8080, une page s'affiche et donne un compte rendu chargement, comme ci-dessous :

**Staffinit**
Environnement: DEV-sqlite  
Nombre de tables: 0 (8 déclarées)  
Nombre de fichiers trouvés: 4
...
si il y a une erreur, il faut corriger et ensuite vous pouvez relancer le lien suivant : http://localhost:8080/start
