# Parameter Sheet

## 1. Server Information

| Hostname | Role | IP Address | OS |
|---|---|---|---|
| WEB01 | Web Server | 192.168.100.10 | Ubuntu Server |
| DB01 | Database Server | 192.168.100.20 | Ubuntu Server |

---

## 2. Network

| Source | Destination | Protocol | Port | Purpose |
|---|---|---|---|---|
| Client | WEB01 | TCP | 80 | HTTP |
| WEB01 | DB01 | TCP | 5432 | PostgreSQL |
| Management PC | WEB01 / DB01 | TCP | 22 | SSH |

---

## 3. WEB01

| Item | Value |
|---|---|
| Hostname | WEB01 |
| IP Address | 192.168.100.10 |
| Web Server | Apache |
| Application | PHP |
| PostgreSQL Extension | php-pgsql |
| Document Root | /var/www/html |
| DB Test File | /var/www/html/dbtest.php |

---

## 4. DB01

| Item | Value |
|---|---|
| Hostname | DB01 |
| IP Address | 192.168.100.20 |
| Database | PostgreSQL 18 |
| Port | 5432 |
| Database Name | webappdb |
| Application User | webuser |

---

## 5. Database Objects

### Roles

| Role | Purpose |
|---|---|
| postgres | PostgreSQL administrator |
| webuser | WEB01からのDB接続用 |
| webappuser | usersテーブル所有者 |

### Tables

| Schema | Table | Owner |
|---|---|---|
| public | employees | postgres |
| public | users | webappuser |

---

## 6. Application DB Connection

| Parameter | Value |
|---|---|
| Host | 192.168.100.20 |
| Port | 5432 |
| Database | webappdb |
| User | webuser |

パスワードなどの認証情報はGitHub上には公開しません。

---

## 7. Service Check Commands

### WEB01

```bash
systemctl status apache2
```

### DB01

```bash
systemctl status postgresql@18-main
```

---

## 8. Connectivity Check

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