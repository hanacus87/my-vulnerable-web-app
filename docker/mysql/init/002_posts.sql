SET NAMES utf8mb4;

CREATE TABLE posts (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  title      VARCHAR(200) NOT NULL,
  content    TEXT         NOT NULL,
  is_public  TINYINT(1)   NOT NULL DEFAULT 0,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_posts_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO posts (user_id, title, content, is_public) VALUES
  (1, 'サービス公開のお知らせ', 'vNet を公開しました。よろしくお願いします。', 1),
  (1, '管理者用メモ',           'このメモは非公開。本人だけが見えるはず。',       0),
  (2, 'はじめまして',           'user です。はじめての投稿です。',               1),
  (2, '個人的なメモ',           '非公開の下書き。他人に見られたくない内容。',     0);
