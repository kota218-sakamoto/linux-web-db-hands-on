# Apache Configuration

WEB01ではApacheをWebサーバーとして使用しています。

## Main Settings

| Item | Value |
|---|---|
| Listen Port | 80 |
| Document Root | /var/www/html |
| VirtualHost | *:80 |
| Error Log | /var/log/apache2/error.log |
| Access Log | /var/log/apache2/access.log |

## Enabled Modules

```text
mpm_prefork_module
php_module
```

PHPをApacheモジュールとして実行する構成です。

## Note

`apache2ctl -S` 実行時に、ServerName未設定による以下の警告を確認しました。

```text
AH00558: Could not reliably determine the server's fully qualified domain name
```

本ハンズオンでは動作に影響しないため、現状のままとしています。