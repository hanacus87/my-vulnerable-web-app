# SQL Injection

## 目次

1. [概要](#1-概要)
2. [脆弱なコードと攻撃](#2-脆弱なコードと攻撃)
3. [原因と修正](#3-原因と修正)
4. [参考](#4-参考)

## 1. 概要

**SQL Injection（SQLi）** とは、ユーザの入力値がSQL文の一部として解釈されてしまう構造を利用して、攻撃者がクエリを改変することができる脆弱性です。

## 2. 脆弱なコードと攻撃

### 脆弱なコード

`src/includes/auth.php`:

```php
function login(string $username, string $password): bool
{
  // SQLi可能(文字列連結)
  $sql = "SELECT id, password_hash FROM users WHERE username = '{$username}'";
  $user = db()->query($sql)->fetch();

  if ($user === false || !password_verify($password, $user['password_hash'])) {
    return false;
  }
}
```

### 攻撃（認証バイパス）

このアプリは、行を取得したあとPHP側で `password_verify()` を実行します。パスワードはbcryptでハッシュ化されており照合はSQLの外側で行われるため、特定のデータ行を返すだけでは認証を通せません。

そこで、攻撃者が平文を知っているパスワードのbcryptハッシュを `UNION SELECT` で偽の行として返させます。`password_verify()` はその偽ハッシュと、攻撃者が入力した平文を照合するので、認証が成立します。

#### 手順

1. 任意の平文（例: `mypass`）のbcryptハッシュを生成する。

   ```bash
   php -r "echo password_hash('mypass', PASSWORD_DEFAULT), PHP_EOL;"
   # 出力例: $2y$12$iYN.6/4AKvRQon9ozSHmaOPbhdkW5MrT6m30haHAdtruaGgm4SHFu
   ```

2. ログインフォームに次を入力する。
   - **ユーザ名**: `zzz' UNION SELECT 'ログインしたいユーザID', '生成したハッシュ' #`
   - **パスワード**: `mypass`

組み立てられる SQL:

```sql
SELECT id, password_hash FROM users
WHERE username = 'zzz'                              -- 実ユーザに当たらず0件
UNION SELECT 1, '$2y$12$iYN...(mypass のハッシュ)'  -- この捏造行が返る
#'
```

3. ログインボタン押下

指定したユーザIDのユーザにログイン可能

## 3. 原因と修正

### 原因

ユーザ入力をSQL文字列に**連結**しているため、データベースが「データ」と「コード（SQL構文）」を区別できません。入力に含まれるクォートや `UNION` がクエリ構造の一部として解釈されてしまいます。

### 修正

値を **プレースホルダ** で渡し、実際の値は `execute()` で別途バインドします。こうすると、渡した値は常にデータとして扱われ、クエリの構造を変えられなくなります。

```diff
// 修正前
- $sql = "SELECT id, password_hash FROM users WHERE username = '{$username}'";
- $user = db()->query($sql)->fetch();

// 修正後
+ $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = ?');
+ $stmt->execute([$username]);
+ $user = $stmt->fetch();
```

> [!Note]
> `src/includes/db.php` はPDOを `ATTR_EMULATE_PREPARES => false`で構成しています。SQLの骨組みをprepare()で先にDBへ渡してコンパイルさせ、実際の値はexecute()でSQLとは別に後から渡します。値と構造が分離されるため、後から渡した値がクエリ構造を変えることはありません。

## 4. 参考

- [OWASP SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
