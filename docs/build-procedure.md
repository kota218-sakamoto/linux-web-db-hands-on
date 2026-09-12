# Build Procedure

## 1. 構築概要

VirtualBox上にUbuntu Serverを2台構築し、WebサーバーとDBサーバーを分離したWeb/DB環境を構築しました。

* WEB01：Apache / PHP
* DB01：PostgreSQL
* WEB01からDB01へTCP/5432で接続
* PHPからPostgreSQLのデータを取得してWebページへ表示

構成は以下の通りです。

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

---

## 2. サーバー構成

| Hostname | Role            | IP Address     |
| -------- | --------------- | -------------- |
| WEB01    | Web Server      | 192.168.100.10 |
| DB01     | Database Server | 192.168.100.20 |

---

## 3. WEB01 構築

### Apache / PHP インストール

パッケージ一覧を更新し、Apache、PHP、PostgreSQL接続用PHPモジュールをインストールします。

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

PostgreSQL管理ユーザーで接続します。

```bash
sudo -u postgres psql
```

---

## 5. データベース・ユーザー作成

### データベース作成

Webアプリケーション用データベースを作成します。

```sql
CREATE DATABASE webappdb;
```

### Webアプリケーション用ユーザー作成

WEB01のPHPからPostgreSQLへ接続するためのユーザーを作成します。

```sql
CREATE USER webuser WITH PASSWORD 'CHANGE_ME';
```

`CHANGE_ME` には実際の環境で使用するパスワードを設定します。

実際のパスワードなどの認証情報はGitHub上には公開しません。

### データベース一覧確認

```sql
\l
```

`webappdb` が存在することを確認します。

### webappdbへ接続

```sql
\c webappdb
```

---

## 6. テーブル作成・データ登録

### employees テーブル作成

```sql
CREATE TABLE employees (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL
);
```

### テストデータ登録

```sql
INSERT INTO employees (name, department)
VALUES
    ('Sato', 'Infrastructure'),
    ('Tanaka', 'Network'),
    ('Suzuki', 'Cloud');
```

### webuserへ権限付与

WEB01から`webuser`を使用して`employees`テーブルを参照できるように権限を付与します。

```sql
GRANT CONNECT ON DATABASE webappdb TO webuser;
GRANT USAGE ON SCHEMA public TO webuser;
GRANT SELECT ON TABLE employees TO webuser;
```

これにより、Webアプリケーション用ユーザーには必要な参照権限のみを付与します。

---

## 7. テーブル・データ確認

`webappdb` に接続した状態でテーブル一覧を確認します。

```sql
\dt
```

以下のテーブルが存在することを確認しました。

| Table     | Owner      |
| --------- | ---------- |
| employees | postgres   |
| users     | webappuser |

### employees テーブル確認

```sql
SELECT * FROM employees;
```

確認結果：

| id | name   | department     |
| -- | ------ | -------------- |
| 1  | Sato   | Infrastructure |
| 2  | Tanaka | Network        |
| 3  | Suzuki | Cloud          |

### users テーブル確認

```sql
SELECT * FROM users;
```

確認結果：

| id | name     |
| -- | -------- |
| 1  | testuser |

PostgreSQLから退出します。

```sql
\q
```

---

## 8. PostgreSQL リモート接続設定

WEB01からDB01のPostgreSQLへ接続できるように設定します。

### postgresql.co
