CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  avatar        VARCHAR(255) NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- テストユーザー
-- admin / admin123
-- user / user123
INSERT INTO users (username, password_hash) VALUES
  ('admin', '$2y$12$MS.VvfmFtdrTPJGLDaizl.f1vqqR8t5fLZrBZ4IiGZzrdhYiIVgrW'),
  ('user',  '$2y$12$Bkd1cm0bNLMfwDLexzc2B.vNu33A8Rp6i1bvMw6NR7OZsRLW4rxn.');
