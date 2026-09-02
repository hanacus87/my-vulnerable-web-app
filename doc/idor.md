# IDOR

## 目次

1. [概要](#1-概要)
2. [脆弱なコードと攻撃](#2-脆弱なコードと攻撃)
3. [原因と修正](#3-原因と修正)
4. [参考](#4-参考)

## 1. 概要

**IDOR（Insecure Direct Object Reference）** とは、リクエストで受け取ったパラメータについて、そのリソースが本人のものか検証せずに処理に使ってしまう構造を利用して、攻撃者が他ユーザのリソースを参照・操作できる脆弱性です。

## 2. 脆弱なコードと攻撃

### 脆弱なコード

`src/mypage.php`:

```php
// IDOR可能(本人確認なし)
$targetId = (int) ($_GET['user_id'] ?? 0);

$stmt = db()->prepare(
  'SELECT id, title, content, is_public, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC'
);
$stmt->execute([$targetId]);
$posts = $stmt->fetchAll();
```

### 攻撃（他ユーザの非公開情報の閲覧）

公開投稿一覧 `posts.php` は `is_public = 1` の投稿しか表示しません。一方 `mypage.php` は、 `user_id` をリクエストから受け取り、 `is_public` で絞り込まずにその `user_id` の全投稿を返します。URLパラメータの `user_id` を書き換えるだけで他ユーザの非公開投稿まで閲覧できます。

#### 手順

1. ログインして自分のマイページを開く。

   ```
   mypage.php?user_id=1
   ```

2. `user_id` を他ユーザの値に書き換える。

   ```
   mypage.php?user_id=2
   ```

3. 他ユーザ（`user_id=2`）の投稿一覧が表示され、非公開の投稿まで閲覧できる。

## 3. 原因と修正

### 原因

リクエスト（GET）で受け取った `user_id` を、**ログイン中ユーザ本人のものか検証せずに**そのまま利用しており、かつ**閲覧対象を攻撃者が自由に指定できる**ため、他ユーザのリソースにアクセスできてしまいます。

### 修正

閲覧対象の `user_id` を**リクエストからは受け取らず、セッションのログイン中ユーザIDに固定**します。こうすると、他ユーザのIDを指定する余地そのものが無くなります。

```diff
// 修正前
- $targetId = (int) ($_GET['user_id'] ?? 0);

// 修正後
+ $targetId = (int) $me['id'];
```

> [!Note]
> この修正により `user_id` パラメータは不要（無効）になります。`src/includes/header.php` のサイドバーリンクや `index.php` / `post_create.php` などのリダイレクトには、パラメータ無し版の `mypage.php` がコメントで用意されています。そちらへ切り替えると、浮いた `user_id` パラメータが消え、導線まで整合します。

## 4. 参考

- [OWASP Insecure Direct Object Reference](https://owasp.org/www-community/attacks/insecure_direct_object_reference)
