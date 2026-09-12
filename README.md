# Linux Web/DB Server Hands-on

Ubuntu Serverを使用して、WebサーバーとDBサーバーを分離したWeb/DB環境を構築したハンズオンです。

## 構成

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
  | PostgreSQL / TCP 5432
  v
DB01
Ubuntu Server
PostgreSQL
192.168.100.20
```

## 使用技術

- Ubuntu Server
- Apache HTTP Server
- PHP
- PostgreSQL 18
- VirtualBox
- SSH
- Linux CLI

## 構築内容

### WEB01

- Ubuntu Serverの構築
- Apacheのインストール・起動
- PHP / php-pgsqlの導入
- DB01上のPostgreSQLへの接続
- PHPからデータベースのデータを取得してHTML表示

### DB01

- Ubuntu Serverの構築
- PostgreSQLのインストール・起動
- データベース作成
- DBユーザー作成
- テーブル作成
- PostgreSQLのリモート接続設定
- `pg_hba.conf` による接続元制御

## 接続構成

| Source | Destination | Protocol / Port | Purpose |
|---|---|---|---|
| Client | WEB01 | HTTP / TCP 80 | Webアクセス |
| WEB01 | DB01 | PostgreSQL / TCP 5432 | DB接続 |
| Management PC | WEB01 / DB01 | SSH / TCP 22 | サーバー管理 |

## 動作確認

以下のコマンドを使用して通信・接続確認を実施しました。

```bash
nc -zv 192.168.100.20 5432
psql -h 192.168.100.20 -U webuser -d webappdb
curl http://localhost/dbtest.php
```

WEB01からDB01へ接続し、PostgreSQLの `employees` テーブルから取得したデータをWebページとして表示できることを確認しました。

## Documents

- [Build Procedure](docs/build-procedure.md)
- [Parameter Sheet](docs/parameter-sheet.md)
- [Test Results](docs/test-results.md)

## Configuration Examples

- [Apache Configuration](configs/apache/README.md)
- [Apache VirtualHost Example](configs/apache/000-default.conf.example)
- [PostgreSQL Configuration](configs/postgresql/postgresql.conf.example)
- [PostgreSQL Access Control](configs/postgresql/pg_hba.conf.example)

## Scripts

- [PHP Database Connection Sample](scripts/dbtest.php)

※ DB接続パスワードなどの認証情報は公開していません。

## Evidence

- [WEB01 Check](evidence/web01-check.txt)
- [DB01 Check](evidence/db01-check.txt)
- [Connectivity Check](evidence/connectivity-check.txt)

## Repository Structure

```text
linux-web-db-hands-on/
├── README.md
├── configs/
│   ├── apache/
│   │   ├── 000-default.conf.example
│   │   └── README.md
│   └── postgresql/
│       ├── postgresql.conf.example
│       └── pg_hba.conf.example
├── docs/
│   ├── build-procedure.md
│   ├── parameter-sheet.md
│   └── test-results.md
├── evidence/
│   ├── web01-check.txt
│   ├── db01-check.txt
│   └── connectivity-check.txt
└── scripts/
    └── dbtest.php
```

## 学習・確認した内容

- Linuxサーバーの基本構築
- Apache / PHPによるWebサーバー構築
- PostgreSQLによるDBサーバー構築
- WebサーバーとDBサーバーの分離
- TCP/5432によるサーバー間通信
- PostgreSQLのリモート接続設定
- `pg_hba.conf` による接続元IP・DBユーザー制御
- PHPからPostgreSQLへの接続
- サービス稼働確認・疎通確認
- 構築手順書、パラメータシート、試験結果、Evidenceの作成