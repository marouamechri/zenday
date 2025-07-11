<?php



use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Chargement des variables d'environnement
if (file_exists(dirname(__DIR__).'/.env.test')) {
    (new Dotenv())->load(dirname(__DIR__).'/.env.test');
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Ensure the test database is ready
if ($_SERVER['APP_ENV'] === 'test') {
    exec('php bin/console doctrine:database:drop --force --env=test');
    exec('php bin/console doctrine:database:create --env=test');
    exec('php bin/console doctrine:schema:create --env=test');
}
