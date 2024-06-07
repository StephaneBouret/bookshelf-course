# On clone le dépot !
git clone https://github.com/StephaneBouret/bookshelf-course.git

# On se déplace dans le dossier
cd bookshelf-course

# On installe les dépendances !
composer install 
npm install

# Dépendances supplémentaires pour noUiSlider et utilisation de YEAR dans le DQL !
npm install nouislider, composer require beberlei/doctrineextensions

# On créé la base de données
php bin/console doctrine:database:create

# On exécute les migrations
php bin/console doctrine:migrations:migrate

# On exécute la fixture
php bin/console doctrine:fixtures:load --no-interaction

# On lance le serveur
php bin/console server:run ou symfony serve

# Lancement des assets
npm run build