# Cookie

## 目次

1. [概要](#1-概要)
2. [属性](#2-属性)
3. [プレフィックス](#3-プレフィックス)
4. [参考](#4-参考)

## 1. 概要

**Cookie** とは、サーバが `Set-Cookie` 応答ヘッダで送り、ブラウザが保存して、以後のリクエストに `Cookie` リクエストヘッダで自動的に付与する `name=value` 形式の小さなデータです。サーバは値に続けて `;` 区切りで属性を指定し、ブラウザは条件に合うリクエストに名前と値だけを送り返します。

```http
Set-Cookie: sid=abc123; Path=/; Secure; HttpOnly; SameSite=Lax
Set-Cookie: tid=def123; Path=/; Secure; HttpOnly; SameSite=Lax
```

```http
Cookie: sid=abc123; tid=def123
```

属性はブラウザに対する指示であり、`Cookie` ヘッダには `name=value` しか乗りません。そのため、サーバは受け取ったCookieがどの属性で保存されたかを検証できません。

### サイトとオリジン

- **オリジン**: スキーム + ホスト + ポートの組です。
- **サイト**: スキーム + 登録可能ドメイン（eTLD+1）の組です。

`https://a.example.com` と `https://b.example.com` は、別オリジンですが同一サイトです。

## 2. 属性

### `Expires`

**`Expires`** は、Cookieの有効期限を絶対時刻で指定する属性です。指定するとブラウザを終了しても残る永続Cookieになります。

```http
Set-Cookie: sid=abc123; Expires=Thu, 01 Oct 2026 00:00:00 GMT
```

### `Max-Age`

**`Max-Age`** は、Cookieの有効期限を受信時点からの秒数で指定する属性です。`Expires` より優先され、どちらも無い場合はブラウザ終了時に破棄されます。

```http
Set-Cookie: sid=abc123; Max-Age=3600
```

### `Domain`

**`Domain`** は、Cookieを送信するホストの範囲を指定する属性です。指定したドメインとそのサブドメインに送信され、未指定の場合は設定したホストにのみ送信されます。

```http
Set-Cookie: sid=abc123; Domain=example.com
```

### `Path`

**`Path`** は、Cookieを送信するURLパスの範囲を指定する属性です。同一オリジン内の分離には使えないため、セキュリティ境界にはなりません。

```http
Set-Cookie: sid=abc123; Path=/
```

### `Secure`

**`Secure`** は、HTTPSのリクエストにのみCookieを送信させる属性です。

```http
Set-Cookie: sid=abc123; Secure
```

### `HttpOnly`

**`HttpOnly`** は、`document.cookie` などのスクリプトからCookieを読めなくする属性です。

```http
Set-Cookie: sid=abc123; HttpOnly
```

### `SameSite`

**`SameSite`** は、クロスサイトのリクエストにCookieを送信するかどうかを指定する属性です。

- **`Strict`**: 同一サイトからのリクエストにのみ送信します。
- **`Lax`**: 同一サイトに加え、クロスサイトのトップレベルナビゲーションのうち、メソッドがGET、HEAD、OPTIONS、TRACEのいずれかであるリクエストにも送信します。
- **`None`**: クロスサイトのリクエストにも送信します。`Secure` 属性の設定が必須です。

```http
Set-Cookie: sid=abc123; SameSite=Lax
```

## 3. プレフィックス

Cookieの名前を特定の文字列で始めると、ブラウザはその文字列に応じてCookieの属性を確認し、条件を満たさない場合は破棄します。サーバは名前を見るだけで、属性が保証されていると判断できます。

### `__Host-`

**`__Host-`** は、そのホスト自身がHTTPSで設定したCookieであることを保証するプレフィックスです。`Secure` 属性があり、`Domain` 属性が無く、`Path=/` が指定されている場合にのみ保存されます。

### `__Secure-`

**`__Secure-`** は、HTTPSで設定されたCookieであることを保証するプレフィックスです。`Secure` 属性がある場合にのみ保存されます。

## 4. 参考

- [RFC 6265 - HTTP State Management Mechanism](https://www.rfc-editor.org/rfc/rfc6265)
- [draft-ietf-httpbis-rfc6265bis - Cookies: HTTP State Management Mechanism](https://datatracker.ietf.org/doc/draft-ietf-httpbis-rfc6265bis/)
