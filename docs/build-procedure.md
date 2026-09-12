# Build Procedure

## 1. 構築概要

VirtualBox上にUbuntu Serverを2台構築し、WebサーバーとDBサーバーを分離した構成を作成しました。

- WEB01：Apache / PHP
- DB01：PostgreSQL
- WEB01からDB01へTCP/5432で接続
- PHPからPostgreSQLのデータを取得してWebページへ表示

---

## 2. サーバー構成

| Hostname | Role | IP Address |
|---|---|---|
| WEB01 | Web Server | 192.168.100.10 |
| DB01 | Database Server | 192.168.100.20 |

---

## 3. WEB01 構築

### Apache / PHP インストール

```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-pgsql -y
```

### Apache 稼働確認

```bash
systemctl status apache2
```

`Active: active (running)` であることを確認しました。

---

## 4. DB01 構築

### PostgreSQL インストール

```bash
sudo apt update
sudo apt install postgresql -y
```

### PostgreSQL 稼働確認

```bash
systemctl status postgresql@18-main
```

`Active: active (running)` であることを確認しました。

### PostgreSQL 接続

```bash
sudo -u postgres psql
```

データベース一覧を確認します。

```sql
\l
```

`webappdb` が存在することを確認しました。

```sql
\c webappdb
```

---

## 5. テーブル確認

`webappdb` に接続した状態で、テーブル一覧を確認します。

```sql
\dt
```

以下のテーブルが存在することを確認しました。

| Table | Owner |
|---|---|
| employees | postgres |
| users | webappuser |

### employees テーブル確認

```sql
SELECT * FROM employees;
```

確認結果：

| id | name | department |
|---|---|---|
| 1 | Sato | Infrastructure |
| 2 | Tanaka | Network |
| 3 | Suzuki | Cloud |

### users テーブル確認

```sql
SELECT * FROM users;
```

確認結果：

| id | name |
|---|---|
| 1 | testuser |

---

## 6. WEB01 → DB01 接続確認

WEB01からDB01のPostgreSQLが使用するTCP/5432へ通信できることを確認します。

```bash
nc -zv 192.168.100.20 5432
```

TCP/5432への接続が成功することを確認しました。

続いて、WEB01からPostgreSQLへ接続します。

```bash
psql -h 192.168.100.20 -U webuser -d webappdb
```

`webappdb` に接続できることを確認しました。

---

## 7. PHP → PostgreSQL 接続確認

WEB01上のPHPから、DB01のPostgreSQLへ接続する構成としました。

接続情報は以下の通りです。

| Parameter | Value |
|---|---|
| Host | 192.168.100.20 |
| Port | 5432 |
| Database | webappdb |
| User | webuser |

PHPでは `pg_connect()` を使用してPostgreSQLへ接続します。

```php
$conn = pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password"
);
```

データベースのパスワードなどの認証情報はGitHub上には公開しません。

---

## 8. Web画面動作確認

WEB01上で以下のコマンドを実行します。

```bash
curl http://localhost/dbtest.php
```

PostgreSQLの `employees` テーブルから取得したデータがHTMLとして表示されることを確認しました。

確認したデータ：

| id | name | department |
|---|---|---|
| 1 | Sato | Infrastructure |
| 2 | Tanaka | Network |
| 3 | Suzuki | Cloud |

これにより、以下の一連の通信が正常に動作していることを確認しました。

```text
Client
  |
  | HTTP
  v
WEB01
Ubuntu Server
Apache / PHP
192.168.100.10
  |
  | TCP/5432
  v
DB01
Ubuntu Server
PostgreSQL
192.168.100.20
```