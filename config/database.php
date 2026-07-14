<?php
/**
 * Database Connection Wrapper (Singleton Pattern)
 */
class Database {
    private static $instance = null;
    private $pdo;

    // El constructor es privado para evitar que alguien use "new Database()"
    private function __construct() {
        // En un entorno real leeríamos de $_ENV. Por ahora, usamos los valores por defecto de Laragon.
        $host = '127.0.0.1';
        $db   = 'uniquiz_db';
        $user = 'root';
        $pass = ''; // En Laragon no hay contraseña por defecto
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Que los errores de SQL rompan la ejecución (para detectarlos rápido)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devolver los datos como arrays asociativos limpios
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Forzar el uso real de Sentencias Preparadas (Seguridad)
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // Si la base de datos falla, detenemos todo y evitamos mostrar la contraseña en pantalla
            error_log($e->getMessage());
            exit('Critical Error: Database connection failed. Check your logs.');
        }
    }

    // Este es el método que llamaremos desde nuestros scripts
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}