# Application de Gestion des Commandes et des Articles

Cette application permet à un utilisateur de créer des commandes, d'ajouter des articles à ces commandes, et de suivre les informations liées aux articles et aux commandes. L'application utilise Symfony 6 et MySQL pour gérer la base de données des commandes et des articles.

## Prérequis

Avant de commencer, assurez-vous d'avoir installé les outils suivants sur votre machine :

- **PHP** (version 8.0 ou supérieure)
- **Composer** (gestionnaire de dépendances PHP)
- **MySQL** (ou un autre SGBD compatible avec Doctrine)
- **Symfony** (recommandé mais pas obligatoire si vous avez installé le projet via Composer)

### Installation


- **Configurer la base de données** :DATABASE_URL="mysql://root:password@127.0.0.1:3306/order_tracking?serverVersion=5.7"

- **Créer la base de données**  php bin/console doctrine:database:create
- **Créer les tables dans la base de données** php bin/console make:migration
                                            php bin/console doctrine:migrations:migrate
- **Supprimer la base de données** php bin/console doctrine:database:drop
- **Installer Doctrine fixture** composer require --dev orm-fixtures
- **Installer faker pour generer des fakes data** composer require fakerphp/faker --dev
- **Charger des données de test**  php bin/console doctrine:fixtures:load
- **Démarrer le serveur de développement** php bin/console server:start

vous pouvez remplacer php bin/console par symfony 


#### Remarques :
- **Composer install** : Cette commande est cruciale pour installer toutes les dépendances du projet.
- **Doctrine fixtures** : Utilisé pour pré-charger la base de données avec des données d'exemple, très utile pendant le développement.
- **Commandes Symfony** : Les commandes Symfony comme `doctrine:database:create` et `migrate` sont utilisées pour configurer et gérer la base de données.



##### Cloner le repository

Si vous n'avez pas encore cloné le projet, commencez par le cloner depuis GitHub :

```bash
git clone https://github.com/username/repository-name.git
cd repository-name