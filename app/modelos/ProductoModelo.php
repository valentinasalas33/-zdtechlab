<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Modelo de productos (Día 13: CRUD completo)
 * Separación en capas: este modelo solo sabe hacer consultas; las
 * decisiones (validar, redirigir, avisar) viven en el controlador.
 */
final class ProductoModelo
{
    private const COLUMNAS_ORDEN = ['nombre', 'precio', 'stock'];

    public function __construct(private PDO $pdo) {}

    public function listar(string $busqueda = '', int $pagina = 1, int $porPagina = 10, string $orden = 'nombre'): array
    {
        $columna = in_array($orden, self::COLUMNAS_ORDEN, true) ? $orden : 'nombre';
        $offset  = max(0, ($pagina - 1) * $porPagina);

        $st = $this->pdo->prepare(
            "SELECT p.id, p.nombre, p.precio, p.stock, p.categoria_id, c.nombre AS categoria
             FROM productos p
             INNER JOIN categorias c ON c.id = p.categoria_id
             WHERE p.activo = 1 AND p.nombre LIKE :b
             ORDER BY p.$columna
             LIMIT :lim OFFSET :off"
        );
        $st->bindValue(':b', '%' . $busqueda . '%');
        $st->bindValue(':lim', $porPagina, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function contar(string $busqueda = ''): int
    {
        $st = $this->pdo->prepare('SELECT COUNT(*) AS total FROM productos WHERE activo = 1 AND nombre LIKE :b');
        $st->bindValue(':b', '%' . $busqueda . '%');
        $st->execute();
        return (int) $st->fetch()['total'];
    }

    public function buscarPorId(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT id, nombre, precio, stock, categoria_id FROM productos WHERE id = :id AND activo = 1');
        $st->execute(['id' => $id]);
        return $st->fetch() ?: null;
    }

    public function crear(array $d): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO productos (nombre, categoria_id, precio, stock, activo, creado_en)
             VALUES (:nombre, :categoria, :precio, :stock, 1, NOW())'
        );
        $st->execute([
            'nombre'    => $d['nombre'],
            'categoria' => $d['categoria_id'],
            'precio'    => $d['precio'],
            'stock'     => $d['stock'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        $st = $this->pdo->prepare(
            'UPDATE productos SET nombre = :nombre, categoria_id = :categoria, precio = :precio, stock = :stock WHERE id = :id'
        );
        return $st->execute([
            'nombre'    => $d['nombre'],
            'categoria' => $d['categoria_id'],
            'precio'    => $d['precio'],
            'stock'     => $d['stock'],
            'id'        => $id,
        ]);
    }

    /** Borrado lógico: el historial de pedidos debe seguir siendo legible */
    public function desactivar(int $id): bool
    {
        return $this->pdo->prepare('UPDATE productos SET activo = 0 WHERE id = :id')->execute(['id' => $id]);
    }
}
