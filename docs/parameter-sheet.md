# パラメータシート

## 1. サーバー情報

| ホスト名 | 役割 | IPアドレス | OS |
|---|---|---|---|
| WEB01 | WEBサーバー | 192.168.100.10 | Ubuntu Server |
| DB01 | DBサーバー | 192.168.100.20 | Ubuntu Server |

---

## 2. ネットワーク

| 送信元 | 宛先 | プロトコル | ポート | 用途 |
|---|---|---|---|---|
| クライアント | WEB01 | TCP | 80 | HTTP |
| WEB01 | DB01 | TCP | 5432 | PostgreSQL |
| 管理PC | WEB01 / DB01 | TCP | 22 | SSH |

---

## 3. WEB01

| 項目 | 値 |
|---|---|
| ホスト名 | WEB01 |
| IPアドレス | 192.168.100.10 |
| WEBサーバー | Apache |
| アプリケーション | PHP |
| PostgreSQL拡張 | php-pgsql |
| ドキュメントルート | /var/www/html |
| DB接続確認ファイル | /var/www/html/dbtest.php |

---

## 4. DB01

| 項目 | 値 |
|---|---|
| ホスト名 | DB01 |
| IPアドレス | 192.168.100.20 |
| データベース | PostgreSQL 18 |
| ポート | 5432 |
| データベース名 | webappdb |
| アプリケーションユーザー | webuser |

---

## 5. データベースオブジェクト

### ロール

| ロール | 用途 |
|---|---|
| postgres | PostgreSQL管理者 |
| webuser | WEB01からのDB接続用 |

### テーブル

| スキーマ | テーブル | 所有者 |
|---|---|---|
| public | employees | postgres |

---

## 6. アプリケーションDB接続

| パラメータ | 値 |
|---|---|
| DBホスト | 192.168.100.20 |
| ポート | 5432 |
| データベース | webappdb |
| ユーザー | webuser |

パスワードなどの認証情報はGitHub上には公開しません。

---

## 7. PostgreSQLリモート接続

WEB01からDB01へ接続できるように、PostgreSQL側で接続元を制御します。

### postgresql.conf

```conf
listen_addresses = 'localhost,192.168.100.20'
```

### pg_hba.conf

```conf
host    webappdb    webuser    192.168.100.10/32    scram-sha-256
```

接続元はWEB01（`192.168.100.10`）のみに限定します。

---

## 8. サービス確認コマンド

### WEB01

```bash
systemctl status apache2
```

### DB01

```bash
systemctl status postgresql@18-main
```

---

## 9. 接続確認

WEB01からDB01のPostgreSQLポートへの疎通確認：

```bash
nc -zv 192.168.100.20 5432
```

PostgreSQL接続確認：

```bash
psql -h 192.168.100.20 -U webuser -d webappdb
```

Web動作確認：

```bash
curl http://localhost/dbtest.php
```
