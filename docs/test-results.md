# Test Results

## 1. 試験概要

WEB01 / DB01 の構築後、各サービスの稼働状態、サーバー間通信、PostgreSQL接続、Web画面からのDBデータ取得を確認しました。

---

## 2. 試験結果

| No. | Test Item | Command / Method | Expected Result | Result |
|---|---|---|---|---|
| 1 | Apache稼働確認 | `systemctl status apache2` | `active (running)` | OK |
| 2 | PostgreSQL稼働確認 | `systemctl status postgresql@18-main` | `active (running)` | OK |
| 3 | WEB01 → DB01 ポート疎通 | `nc -zv 192.168.100.20 5432` | TCP/5432への接続成功 | OK |
| 4 | WEB01 → PostgreSQL接続 | `psql -h 192.168.100.20 -U webuser -d webappdb` | `webappdb`へ接続成功 | OK |
| 5 | テーブル確認 | `\dt` | `employees` / `users` が表示される | OK |
| 6 | employeesデータ確認 | `SELECT * FROM employees;` | 3件のテストデータが表示される | OK |
| 7 | usersデータ確認 | `SELECT * FROM users;` | `testuser` が表示される | OK |
| 8 | Web動作確認 | `curl http://localhost/dbtest.php` | DBデータを含むHTMLが返却される | OK |

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

PostgreSQLの `employees` テーブルから取得したデータがHTMLとして返却されることを確認しました。

これにより、

```text
Client
  |
  | HTTP
  v
WEB01
Apache / PHP
  |
  | TCP/5432
  v
DB01
PostgreSQL
```

の一連の通信が正常に動作していることを確認しました。

---

## 9. Test Summary

全試験項目で正常動作を確認しました。

- Apache：正常
- PostgreSQL：正常
- WEB01 → DB01通信：正常
- PostgreSQL接続：正常
- PHP → PostgreSQL接続：正常
- Web画面へのDBデータ表示：正常