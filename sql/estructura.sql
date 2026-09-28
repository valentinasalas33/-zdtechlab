-- ============================================
-- ZD.TechLab — Estructura de la base de datos
-- Día 9: seis tablas, llaves foráneas, tipos e índices
-- ============================================

CREATE DATABASE IF NOT EXISTS zdtechlab
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE zdtechlab;

-- ---------- Tabla: usuarios (acceso al sistema, no es "contenido" del negocio) ----------
CREATE TABLE usuarios (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre          VARCHAR(100)  NOT NULL,
  correo          VARCHAR(150)  NOT NULL,
  clave_hash      VARCHAR(255)  NOT NULL,
  rol             ENUM('administrador','vendedor','consultor') NOT NULL DEFAULT 'consultor',
  activo          TINYINT(1)    NOT NULL DEFAULT 1,
  bloqueado_hasta DATETIME      NULL,
  creado_en       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_correo (correo)
) ENGINE=InnoDB;

-- Registro de intentos de acceso, para el bloqueo por intentos fallidos (día 10)
CREATE TABLE intentos_acceso (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  correo     VARCHAR(150) NOT NULL,
  exitoso    TINYINT(1)   NOT NULL,
  creado_en  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_intentos_correo_fecha (correo, creado_en)
) ENGINE=InnoDB;

-- ---------- Tabla: categorias ----------
CREATE TABLE categorias (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre  VARCHAR(80) NOT NULL,
  UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB;

-- ---------- Tabla: productos ----------
CREATE TABLE productos (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id  INT UNSIGNED NOT NULL,
  nombre        VARCHAR(150) NOT NULL,
  precio        DECIMAL(12,2) NOT NULL,
  stock         INT UNSIGNED NOT NULL DEFAULT 0,
  stock_minimo  INT UNSIGNED NOT NULL DEFAULT 5,
  activo        TINYINT(1) NOT NULL DEFAULT 1,
  creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_productos_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_productos_nombre (nombre),
  INDEX idx_productos_categoria (categoria_id)
) ENGINE=InnoDB;

-- ---------- Tabla: clientes ----------
CREATE TABLE clientes (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(150) NOT NULL,
  documento  VARCHAR(30)  NOT NULL,
  correo     VARCHAR(150) NULL,
  creado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_clientes_documento (documento)
) ENGINE=InnoDB;

-- ---------- Tabla: pedidos ----------
CREATE TABLE pedidos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id  INT UNSIGNED NOT NULL,
  fecha       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total       DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado      ENUM('confirmado','anulado') NOT NULL DEFAULT 'confirmado',
  CONSTRAINT fk_pedidos_cliente
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_pedidos_fecha (fecha),
  INDEX idx_pedidos_cliente (cliente_id)
) ENGINE=InnoDB;

-- ---------- Tabla puente: detalle_pedido (relación muchos-a-muchos) ----------
CREATE TABLE detalle_pedido (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id        INT UNSIGNED NOT NULL,
  producto_id      INT UNSIGNED NOT NULL,
  cantidad         INT UNSIGNED NOT NULL,
  precio_unitario  DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_detalle_pedido
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_detalle_producto
    FOREIGN KEY (producto_id) REFERENCES productos(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_detalle_pedido (pedido_id),
  INDEX idx_detalle_producto (producto_id)
) ENGINE=InnoDB;
