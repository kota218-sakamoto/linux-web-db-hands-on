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

* Ubuntu Server
* Apache HTTP Server
* PHP
* PostgreSQL
* VirtualBox
* SSH
* Linux CLI

## 構築内容

### WEB01

* Ubuntu Serverの構築
* Apacheのインストール・起動
* PHPの導入
* php-pgsqlの導入
* DB01上のPostgreSQLへの接続
* PHPからデータベースのデータを取得してHTML表示

### DB01

* Ubuntu Serverの構築
* PostgreSQLのインストール・起動
* データベース作成
* DBユーザー作成
* テーブル作成
* WEB01からのリモート接続設定

## 動作確認

以下のコマンドを使用して通信・接続確認を実施しました。

```bash
nc -zv 192.168.100.20 5432

psql -h 192.168.100.20 -U webuser -d webappdb

curl http://localhost/dbtest.php
```

WEB01からDB01へ接続し、PostgreSQLの`employees`テーブルから取得したデータをWebページとして表示できることを確認しました。

## Repository Structure

```text
linux-web-db-hands-on/
├── README.md
├── configs/
├── docs/
├── evidence/
└── scripts/
```

今後、構築手順、設定ファイル、テスト結果、証跡を各ディレクトリに追加していきます。
