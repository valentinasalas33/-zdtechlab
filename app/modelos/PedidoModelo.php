<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Modelo de pedidos (Día 13)
 * registrar() usa una transacción: o se guarda el pedido completo (cabecera,
 * detalle y descuento de stock), o no se guarda nada si algo falla.
 */
final class PedidoModelo
{
    public function __construct(private PDO $pdo) {}

    public function listar(int $pagina = 1, int $porPagina = 10): array
    {
        $offset = max(0, ($pagina - 1) * $porPagina);
        $st = $this->pdo->prepare(
            'SELECT p.id, c.nombre AS cliente, p.fecha, p.total, p.estado
             FROM pedidos p
             INNER JOIN clientes c ON c.id = p.cliente_id
             ORDER BY p.fecha DESC
             LIMIT :lim OFFSET :off'
        );
        $st->bindValue(':lim', $porPagina, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function contar(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) AS total FROM pedidos')->fetch()['total'];
    }

    /**
     * @param array<int, array{id:int, cant:int}> $items
     * @throws RuntimeException si no hay stock suficiente o el producto no existe
     */
    public function registrar(int $clienteId, array $items): int
    {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        try {
            // El precio y el stock se leen de la base de datos, nunca del formulario
            $precios = [];
            foreach ($items as $it) {
                $st = $pdo->prepare('SELECT precio, stock FROM productos WHERE id = :id AND activo = 1');
                $st->execute(['id' => $it['id']]);
                $producto = $st->fetch();
                if (!$producto || (int) $producto['stock'] < $it['cant']) {
                    throw new RuntimeException('Stock insuficiente o producto no encontrado.');
                }
                $precios[$it['id']] = (float) $producto['precio'];
            }

            $total = array_sum(array_map(fn ($it) => $precios[$it['id']] * $it['cant'], $items));

            $pdo->prepare('INSERT INTO pedidos (cliente_id, fecha, total) VALUES (:c, NOW(), :t)')
                ->execute(['c' => $clienteId, 't' => $total]);
            $pedidoId = (int) $pdo->lastInsertId();

            $detalle = $pdo->prepare(
                'INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (:p, :pr, :c, :pu)'
            );
            $descontarStock = $pdo->prepare('UPDATE productos SET stock = stock - :c WHERE id = :id');

            foreach ($items as $it) {
                $detalle->execute(['p' => $pedidoId, 'pr' => $it['id'], 'c' => $it['cant'], 'pu' => $precios[$it['id']]]);
                $descontarStock->execute(['c' => $it['cant'], 'id' => $it['id']]);
            }

            $pdo->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            $pdo->rollBack(); // o todo, o nada
            throw $e;
        }
    }
}
