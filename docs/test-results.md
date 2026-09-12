# Test Results

## 1. 試験概要

WEB01 / DB01 の構築後、各サービスの稼働状態、サーバー間通信、PostgreSQL接続、WebアプリケーションからのDBデータ取得を確認しました。

また、ApacheおよびPostgreSQLを意図的に停止し、サービス停止時の影響確認、ログ確認、サービス復旧後の正常性確認を実施しました。

---

## 2. 正常系試験結果

| No. | Test Item | Command / Method | Expected Result | Result |
|---|---|---|---|---|
| 1 | Apache稼働確認 | `systemctl status apache2` | `active (running)` | PASS |
| 2 | PostgreSQL稼働確認 | `systemctl status postgresql@18-main` | `active (running)` | PASS |
| 3 | WEB01 → DB01 ポート疎通 | `nc -zv 192.168.100.20 5432` | TCP/5432への接続成功 | PASS |
| 4 | WEB01 → PostgreSQL接続 | `psql -h 192.168.100.20 -U webuser -d webappdb` | `webappdb`へ接続成功 | PASS |
| 5 | テーブル確認 | `\dt` | `employees` / `users` が表示される | PASS |
| 6 | employeesデータ確認 | `SELECT * FROM employees;` | 3件のテストデータが表示される | PASS |
| 7 | usersデータ確認 | `SELECT * FROM users;` | `testuser` が表示される | PASS |
| 8 | Web動作確認 | `curl http://localhost/dbtest.php` | DBデータを含むHTMLが返却される | PASS |

---

## 3. Apache 稼働確認

WEB01で以下を実行しました。

```bash
systemctl status apache2
```

確認結果：

```text
Active: active (running)
```

Apacheが正常に稼働していることを確認しました。

---

## 4. PostgreSQL 稼働確認

DB01で以下を実行しました。

```bash
systemctl status postgresql@18-main
```

確認結果：

```text
Active: active (running)
```

PostgreSQLクラスタが正常に稼働していることを確認しました。

---

## 5. WEB01 → DB01 通信試験

WEB01からDB01のPostgreSQLポートへの疎通を確認しました。

```bash
nc -zv 192.168.100.20 5432
```

TCP/5432への接続が成功することを確認しました。

---

## 6. PostgreSQL 接続試験

WEB01からDB01上のPostgreSQLへ接続しました。

```bash
psql -h 192.168.100.20 -U webuser -d webappdb
```

`webappdb` に正常に接続できることを確認しました。

---

## 7. Database Test

### テーブル一覧

```sql
\dt
```

確認結果：

| Schema | Table | Owner |
|---|---|---|
| public | employees | postgres |
| public | users | webappuser |

### employees

```sql
SELECT * FROM employees;
```

確認結果：

| id | name | department |
|---|---|---|
| 1 | Sato | Infrastructure |
| 2 | Tanaka | Network |
| 3 | Suzuki | Cloud |

### users

```sql
SELECT * FROM users;
```

確認結果：

| id | name |
|---|---|
| 1 | testuser |

---

## 8. Web / Database Integration Test

WEB01上で以下を実行しました。

```bash
curl http://localhost/dbtest.php
```

PostgreSQLの`employees`テーブルから取得したデータがHTMLとして返却されることを確認しました。

通信経路：

```text
Client
  |
  | HTTP
  v
WEB01
Apache / PHP
192.168.100.10
  |
  | TCP/5432
  v
DB01
PostgreSQL
192.168.100.20
```

これにより、Apache / PHPからPostgreSQLまでの一連の通信が正常に動作していることを確認しました。

---

## 9. Apache Failure / Recovery Test

WEB01上のApacheを意図的に停止し、Webサービスへの影響、ログ、および復旧後の動作を確認しました。

### 試験結果

| No. | Test Item | Expected Result | Result |
|---|---|---|---|
| 1 | Apache停止 | `inactive (dead)`になる | PASS |
| 2 | Webアクセス確認 | HTTP接続に失敗する | PASS |
| 3 | Apacheログ確認 | サービス停止ログを確認できる | PASS |
| 4 | Apache起動 | `active (running)`になる | PASS |
| 5 | Web復旧確認 | `dbtest.php`が正常表示される | PASS |

### 障害発生

Apacheを停止しました。

```bash
sudo systemctl stop apache2
```

状態を確認しました。

```bash
systemctl status apache2
```

確認結果：

```text
Active: inactive (dead)
```

Apache停止後、Webアクセスを確認しました。

```bash
curl http://localhost/dbtest.php
```

TCP/80への接続に失敗し、Webページへアクセスできないことを確認しました。

### ログ確認

```bash
sudo journalctl -u apache2 --since "10 minutes ago" --no-pager
```

Apacheサービスが停止されたことをログから確認しました。

### 復旧

Apacheを起動しました。

```bash
sudo systemctl start apache2
```

状態を確認しました。

```bash
systemctl status apache2
```

確認結果：

```text
Active: active (running)
```

Webアプリケーションを再確認しました。

```bash
curl http://localhost/dbtest.php
```

`Employee List`および`employees`テーブルのデータが再表示され、Webサービスが正常に復旧したことを確認しました。

### Evidence

#### Apache Failure

![Apache Failure](../evidence/apache-failure.png)

#### Apache Recovery

![Apache Recovery](../evidence/apache-recovery.png)

---

## 10. PostgreSQL Failure / Recovery Test

DB01上のPostgreSQLを意図的に停止し、WEB01からのDB接続およびWebアプリケーションへの影響、ログ、復旧後の動作を確認しました。

### 試験結果

| No. | Test Item | Expected Result | Result |
|---|---|---|---|
| 1 | PostgreSQL正常確認 | `employees`テーブルを参照できる | PASS |
| 2 | PostgreSQL停止 | `inactive (dead)`になる | PASS |
| 3 | TCP/5432接続確認 | WEB01からの接続に失敗する | PASS |
| 4 | PHP動作確認 | DB接続に失敗する | PASS |
| 5 | PostgreSQLログ確認 | サービス停止ログを確認できる | PASS |
| 6 | PostgreSQL起動 | `active (running)`になる | PASS |
| 7 | TCP/5432復旧確認 | WEB01から接続できる | PASS |
| 8 | Web/DB復旧確認 | `employees`のデータが再表示される | PASS |

### 障害前正常確認

DB01でPostgreSQLが正常に稼働していることを確認しました。

```bash
systemctl status postgresql@18-main
```

続いて、`employees`テーブルのデータを確認しました。

```bash
sudo -u postgres psql -d webappdb -c "SELECT * FROM employees;"
```

3件のテストデータが正常に取得できることを確認しました。

### 障害発生

PostgreSQLを停止しました。

```bash
sudo systemctl stop postgresql@18-main
```

状態を確認しました。

```bash
systemctl status postgresql@18-main
```

確認結果：

```text
Active: inactive (dead)
```

### WEB01からの影響確認

WEB01からDB01のTCP/5432へ接続しました。

```bash
nc -zv 192.168.100.20 5432
```

確認結果：

```text
Connection refused
```

PostgreSQL停止中はTCP/5432へ接続できないことを確認しました。

続いて、WEB01のWebアプリケーションを確認しました。

```bash
curl http://localhost/dbtest.php
```

確認結果：

```text
Database connection failed.
```

これにより、DBサービスの停止がWebアプリケーションに影響することを確認しました。

### ログ確認

DB01でPostgreSQLのサービスログを確認しました。

```bash
sudo journalctl -u postgresql@18-main --since "10 minutes ago" --no-pager
```

PostgreSQLサービスの停止処理が正常に実行されたことを確認しました。

### 復旧

PostgreSQLを起動しました。

```bash
sudo systemctl start postgresql@18-main
```

状態を確認しました。

```bash
systemctl status postgresql@18-main
```

確認結果：

```text
Active: active (running)
```

WEB01からTCP/5432への接続を再確認しました。

```bash
nc -zv 192.168.100.20 5432
```

TCP/5432への接続が成功することを確認しました。

最後にWebアプリケーションを確認しました。

```bash
curl http://localhost/dbtest.php
```

`Employee List`および`employees`テーブルの3件のデータが再表示され、Web / DB連携が正常に復旧したことを確認しました。

### Evidence

#### PostgreSQL Failure

![PostgreSQL Failure](../evidence/postgresql-failure.png)

#### Web Application Impact

![PostgreSQL Web Impact](../evidence/postgresql-web-impact.png)

#### PostgreSQL Log / Recovery

![PostgreSQL Log Recovery](../evidence/postgresql-log-recovery.png)

#### Web / Database Recovery

![PostgreSQL Web Recovery](../evidence/postgresql-web-recovery.png)

---

## 11. Test Summary

正常系試験および障害・復旧試験を実施し、すべての試験項目で期待した結果を確認しました。

| Category | Result |
|---|---|
| Apache稼働確認 | PASS |
| PostgreSQL稼働確認 | PASS |
| WEB01 → DB01通信 | PASS |
| PostgreSQL接続 | PASS |
| PHP → PostgreSQL連携 | PASS |
| Web画面へのDBデータ表示 | PASS |
| Apache障害検知 | PASS |
| Apacheログ確認 | PASS |
| Apache復旧 | PASS |
| PostgreSQL障害検知 | PASS |
| PostgreSQL停止時のWeb影響確認 | PASS |
| PostgreSQLログ確認 | PASS |
| PostgreSQL復旧 | PASS |

今回の試験により、通常時のWeb / DB連携だけでなく、ApacheおよびPostgreSQLのサービス停止時に発生する影響を確認し、ログを用いた状態確認とサービス復旧後の正常性確認まで実施しました。