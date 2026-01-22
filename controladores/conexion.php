<?php
// UQ Lead Dev: controladores/conexion.php
// Objetivo: Conexión centralizada a la BBDD usando PDO.

// Configuración de credenciales (Ajustar para producción en el servidor 138.100.77.177)
$host = 'localhost';     // En Laragon local suele ser localhost
$db   = 'uniquiz_db';    // El nombre que definimos en bbdd.sql
$user = 'root';          // Usuario por defecto en Laragon
$pass = '';              // Contraseña vacía por defecto en Laragon
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en caso de error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa sentencias preparadas reales (seguridad)
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En producción no deberíamos mostrar el error exacto al usuario, pero para depurar sí.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>