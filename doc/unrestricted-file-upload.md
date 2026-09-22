# Unrestricted File Upload

## 目次

1. [概要](#1-概要)
2. [攻撃](#2-攻撃)
3. [原因と修正](#3-原因と修正)
4. [参考](#4-参考)

## 1. 概要

**Unrestricted File Upload** とは、アップロードされるファイルの拡張子・種類・中身を検証せずに受け入れてしまうことで、攻撃者に危険なファイルを設置されてしまう脆弱性です。

## 2. 攻撃

### 攻撃（Webシェル設置によるRCE）

プロフィール画像の代わりにPHPのWebシェルをアップロードすることで、攻撃者がアップロードしたWebシェルにリクエストを送り、サーバ上で任意コードを実行する**RCE**が成立します。

#### 手順

1. ログインし、プロフィール更新フォームを開く。

2. 次の内容のPNGファイルを用意する。

   ```php
   <?php system($_GET['cmd']); ?>
   ```

3. アイコン更新のリクエストをBurp Suiteなどでプロキシし、拡張子を `.php` に変更してから送信する。

4. 保存されたWebシェルにWeb経由でアクセスすると、サーバ上でパラメータ通りのコマンドが実行される。

   ```
   http://localhost:8080/uploads/avatars/shell.php?cmd=id
   ```

## 3. 原因と修正

### 原因

**アップロードファイルの拡張子や中身をサーバで検証せず**、`.php` を含む任意のファイルを受け入れていることが直接の原因です。さらに、**アップロードディレクトリでPHPの実行を抑止していない**ため、保存されたPHPがそのままWebから実行されます。この2つが重なることで、単なるファイルアップロードがRCEにまで発展します。

### 修正

まず、サーバでMIMEタイプと画像判定・拡張子のホワイトリスト検証を行い、保存名もサーバ側で生成します。こうすると画像以外のファイルは弾かれ、`.php` を保存できなくなります。

```diff
// 修正前
- $newName = $_FILES['avatar']['full_path'];

// 修正後
+ $finfo = finfo_open(FILEINFO_MIME_TYPE);
+ $mime = finfo_file($finfo, (string) $file['tmp_name']);
+ finfo_close($finfo);
+
+ $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
+ if (!isset($allowed[$mime]) || getimagesize((string) $file['tmp_name']) === false) {
+   flashRedirect('error', 'JPEGまたはPNG画像のみアップロードできます。', $backTo);
+ }
+
+ $newName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
```

加えて、多層防御として保存先ディレクトリでのPHP実行そのものを止めます。`src/uploads/.htaccess` を次の内容で有効化します。

```diff
+ php_flag engine off
+ <FilesMatch "\.(php|phtml|phar|php[0-9]?)$">
+   Require all denied
+ </FilesMatch>
```

## 4. 参考

- [OWASP Unrestricted File Upload](https://owasp.org/www-community/vulnerabilities/Unrestricted_File_Upload)
