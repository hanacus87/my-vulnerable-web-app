# CSRF

## 目次

1. [概要](#1-概要)
2. [攻撃](#2-攻撃)
3. [原因と修正](#3-原因と修正)
4. [参考](#4-参考)

## 1. 概要

**CSRF（Cross-Site Request Forgery）** とは、被害者のブラウザを利用して、被害者本人が意図しないリクエストを、攻撃者が用意した外部サイトなどから正規サイトへ送信させる脆弱性です。

## 2. 攻撃

### 攻撃（なりすまし投稿）

攻撃者は `post_create.php` へ自動送信するフォームを外部サイトに設置します。被害者がそのページを開いた瞬間、条件が揃えば被害者のブラウザは被害者のCookie付きでPOSTを送信し、被害者名義の投稿が作成されます。

#### 手順

1. 被害者が通常どおりアプリにログインしておく。

2. 被害者が、攻撃者の用意したページ（ `poc/csrf.html` ）を開くと、読み込みと同時にフォームが自動送信される。

3. 被害者の意図しないポストが投稿されている。

## 3. 原因と修正

### 原因

原因は2つあります。

1つ目は、**リクエストが自サイトのリクエスト由来か**を検証していないことです。条件が揃えばブラウザはクロスサイトのリクエストにもCookieを自動付与するため、サーバはリクエストが自サイトのものかを判定できません。

2つ目は、セッションCookieに **`SameSite` 属性を明示していない**ことです。`SameSite` はクロスサイトのリクエストにCookieを送るかをブラウザ側で制御する属性で、適切に設定すればクロスサイトからのCookie送信自体を抑えられます。

> [!Note]
> `SameSite` 属性未指定時の既定の扱い
>
> - **Chrome**: 未指定は既定で `Lax` 扱い（Chrome 80 以降）。ただし「Lax+POST」例外により、未指定で既定 `Lax` になったCookieは作成後約2分以内ならクロスサイトのトップレベルPOSTにも送信されます。この例外はサブリソース（`fetch` 等）には適用されず、明示的な `SameSite=Lax` にも適用されません。
> - **Edge**: Chromiumベースのため挙動はChromeと同様です。
> - **Firefox**: リリース版は Lax-by-default を適用しないため、未指定は `None` 相当として扱われます。
> - **Safari**: Lax-by-default を適用しないため、未指定は `None` 相当として扱われます。

### 修正

原因に対応して2層で対策します。主対策は原因の1つ目に対応する**CSRFトークン**です。原因の2つ目に対応する **`SameSite` の明示的な設定**は多層防御で、主対策と併用します。

#### 主対策: CSRFトークンの発行・埋め込みと検証

推測が難しいトークンによってCSRFを対策します。

**①発行**（ `src/includes/auth.php` ）：セッションに紐付けてCSRFトークンを生成

```php
function csrfToken(): string
{
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}
```

**②フォームへの埋め込み**（ `src/mypage.php` などの各フォーム）：各フォームにCSRFトークンを埋め込み

```diff
  <form method="post" action="post_create.php">
+   <input type="hidden" name="_token" value="<?= h(csrfToken()) ?>">
    ...
  </form>
```

**③受け側での検証**（ `src/includes/auth.php` ）：リクエストのCSRFトークンを定数時間で評価

```php
function verifyCsrf(): void
{
  $sent = $_POST['_token'] ?? '';
  if (!is_string($sent) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
    http_response_code(400);
    exit('Invalid CSRF token');
  }
}
```

#### 多層防御: `SameSite` の明示的な設定

Cookieに `SameSite` 属性を明示的に設定します。

```diff
+ session_set_cookie_params([
+   'samesite' => 'Lax',
+ ]);
  session_start();
```

これは主対策の代わりではなく、上乗せの層です。効果と限界は次のとおりです。

- **効果**: 明示的な `SameSite=Lax` は、未指定時の例外の対象外になります。そのため、フォームを利用したログイン直後のクロスサイトPOSTは成立しません。
- **限界**: 同一サイトからの攻撃や、GET遷移による状態変更は防げません。

> [!Warning]
> **アプリケーションにXSS可能な脆弱性があると、上記2層のCSRF対策はどちらも無力化されます。**
>
> XSSで注入されたスクリプトはアプリ自身のオリジンで実行されるため、
>
> - DOMなどから正規のCSRFトークンを読み取り、検証を通過する偽造リクエストの送信が可能
> - リクエストが同一サイト由来になるため、 `SameSite` に関わらずブラウザがCookieを送信
>
> CSRF対策の前提として、XSS自体を対策する必要があります。

## 4. 参考

- [OWASP Cross Site Request Forgery (CSRF)](https://owasp.org/www-community/attacks/csrf)
