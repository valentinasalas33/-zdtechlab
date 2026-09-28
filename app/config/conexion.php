<?php
declare(strict_types=1);

final class Conexion
{
    private static ?PDO $pdo = null;

    public static function obtener(): PDO
    {
        if (self::$pdo === null) {
            $cfg = require __DIR__ . '/credenciales.php'; // excluido del repositorio
            $dsn = "mysql:host={$cfg['host']};dbname={$cfg['bd']};charset=utf8mb4";

            self::$pdo = new PDO($dsn, $cfg['usuario'], $cfg['clave'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }
}
