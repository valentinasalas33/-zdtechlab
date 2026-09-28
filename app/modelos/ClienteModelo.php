<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Modelo de clientes (Día 13: CRUD completo)
 */
final class ClienteModelo
{
    public function __construct(private PDO $pdo) {}

    public function listar(string $busqueda = '', int $pagina = 1, int $porPagina = 10): array
    {
        $offset = max(0, ($pagina - 1) * $porPagina);
        $st = $this->pdo->prepare(
            'SELECT id, nombre, documento, correo FROM clientes
             WHERE nombre LIKE :b1 OR documento LIKE :b2
             ORDER BY nombre
             LIMIT :lim OFFSET :off'
        );
        $st->bindValue(':b1', '%' . $busqueda . '%');
        $st->bindValue(':b2', '%' . $busqueda . '%');
        $st->bindValue(':lim', $porPagina, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function contar(string $busqueda = ''): int
    {
        $st = $this->pdo->prepare('SELECT COUNT(*) AS total FROM clientes WHERE nombre LIKE :b1 OR documento LIKE :b2');
        $st->bindValue(':b1', '%' . $busqueda . '%');
        $st->bindValue(':b2', '%' . $busqueda . '%');
        $st->execute();
        return (int) $st->fetch()['total'];
    }

    public function buscarPorId(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT id, nombre, documento, correo FROM clientes WHERE id = :id');
        $st->execute(['id' => $id]);
        return $st->fetch() ?: null;
    }

    public function existeDocumento(string $documento, ?int $excluirId = null): bool
    {
        $sql = 'SELECT id FROM clientes WHERE documento = :doc';
        $parametros = ['doc' => $documento];
        if ($excluirId !== null) {
            $sql .= ' AND id != :id';
            $parametros['id'] = $excluirId;
        }
        $st = $this->pdo->prepare($sql);
        $st->execute($parametros);
        return (bool) $st->fetch();
    }

    public function crear(array $d): int
    {
        $st = $this->pdo->prepare('INSERT INTO clientes (nombre, documento, correo) VALUES (:nombre, :documento, :correo)');
        $st->execute($d);
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        $st = $this->pdo->prepare('UPDATE clientes SET nombre = :nombre, documento = :documento, correo = :correo WHERE id = :id');
        return $st->execute($d + ['id' => $id]);
    }

    /** Aquí NO hay borrado lógico (la tabla clientes no tiene columna activo);
     *  se intenta un borrado real y se deja que la restricción de la llave
     *  foránea (ON DELETE RESTRICT en pedidos) impida romper el historial. */
    public function eliminar(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM clientes WHERE id = :id')->execute(['id' => $id]);
    }
}
