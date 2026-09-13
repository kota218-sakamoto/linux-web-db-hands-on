# 構築手順書

## 1. 構築概要

VirtualBox上にUbuntu Serverを2台構築し、WebサーバーとDBサーバーを分離したWeb/DB環境を構築しました。

- WEB01：Apache / PHP
- DB01：PostgreSQL
- WEB01からDB01へTCP/5432で接続
- PHPからPostgreSQLのデータを取得してWebページへ表示

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

| ホスト名 | 役割 | IPアドレス |
|---|---|---|
| WEB01 | Web Server | 192.168.100.10 |
| DB01 | Database Server | 192.168.100.20 |

---

## 3. WEB01構築

### Apache / PHPインストール

パッケージ一覧を更新し、Apache、PHP、PostgreSQL接続用PHPモジュールをインストールします。

```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-pgsql -y
```

### Apache稼働確認

```bash
systemctl status apache2
```

`Active: active (running)` であることを確認しました。

---

## 4. DB01構築

### PostgreSQLインストール

```bash
sudo apt update
sudo apt install postgresql -y
```

### PostgreSQL稼働確認

```bash
systemctl status postgresql@18-main
```

`Active: active (running)` であることを確認しました。

### PostgreSQL接続

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

### WEBアプリケーション用ユーザー作成

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

### employeesテーブル作成

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

| テーブル | 所有者 |
|---|---|
| employees | postgres |

### employeesテーブル確認

```sql
SELECT * FROM employees;
```

確認結果：

| id | name | department |
|---|---|---|
| 1 | Sato | Infrastructure |
| 2 | Tanaka | Network |
| 3 | Suzuki | Cloud |

PostgreSQLから退出します。

```sql
\q
```

---

## 8. PostgreSQLリモート接続設定

WEB01からDB01のPostgreSQLへ接続できるように設定します。

### postgresql.conf設定

PostgreSQLの設定ファイルを編集します。

```bash
sudo nano /etc/postgresql/18/main/postgresql.conf
```

`listen_addresses` を設定します。

```conf
listen_addresses = 'localhost,192.168.100.20'
```

これにより、ローカルホスト以外からのPostgreSQL接続を受け付けられるようにします。

### pg_hba.conf設定

接続元をWEB01のみに制限するため、`pg_hba.conf` を編集します。

```bash
sudo nano /etc/postgresql/18/main/pg_hba.conf
```

以下を追加します。

```conf
host    webappdb    webuser    192.168.100.10/32    scram-sha-256
```

これにより、`webappdb`へ`webuser`で接続できるホストをWEB01（`192.168.100.10`）に限定します。

### PostgreSQL再起動

設定を反映します。

```bash
sudo systemctl restart postgresql@18-main
```

状態を確認します。

```bash
systemctl status postgresql@18-main
```

`Active: active (running)` であることを確認します。

---

## 9. WEB01 → DB01接続確認

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

接続後、`employees`テーブルを参照します。

```sql
SELECT * FROM employees;
```

以下のデータを取得できることを確認しました。

| id | name | department |
|---|---|---|
| 1 | Sato | Infrastructure |
| 2 | Tanaka | Network |
| 3 | Suzuki | Cloud |

---

## 10. PHP → PostgreSQL接続設定

WEB01上のPHPからDB01のPostgreSQLへ接続します。

接続情報は以下の通りです。

| パラメータ | 値 |
|---|---|
| DBホスト | 192.168.100.20 |
| ポート | 5432 |
| データベース | webappdb |
| ユーザー | webuser |
| Password | GitHub上では非公開 |

PHPでは`pg_connect()`を使用してPostgreSQLへ接続します。

```php
$conn = pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password"
);
```

データベースのパスワードなどの認証情報はGitHub上には公開しません。

---

## 11. WEB画面動作確認

WEB01上で以下のコマンドを実行します。

```bash
curl http://localhost/dbtest.php
```

PostgreSQLの`employees`テーブルから取得したデータがHTMLとして表示されることを確認しました。

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

---

## 12. 構築結果

以下の項目が正常に動作することを確認しました。

- Apacheの起動
- PHPの実行
- PostgreSQLの起動
- `webappdb`データベースの作成
- `webuser`ユーザーの作成
- `employees`テーブルの作成
- `webuser`への必要最小限の参照権限設定
- WEB01からDB01へのTCP/5432接続
- WEB01からPostgreSQLへのログイン
- PHPからPostgreSQLへの接続
- PostgreSQLから取得したデータのWebページ表示

これにより、WebサーバーとDBサーバーを分離した基本的なWeb/DBシステムを構築し、ネットワーク疎通からアプリケーションレベルの動作まで確認しました。
