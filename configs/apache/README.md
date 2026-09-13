# Apache設定

WEB01ではApacheをWebサーバーとして使用しています。

## 主な設定

| 項目 | 値 |
|---|---|
| リッスンポート | 80 |
| ドキュメントルート | /var/www/html |
| VirtualHost | *:80 |
| エラーログ | /var/log/apache2/error.log |
| アクセスログ | /var/log/apache2/access.log |

## 有効化モジュール

```text
mpm_prefork_module
php_module
```

PHPをApacheモジュールとして実行する構成です。

## 補足

`apache2ctl -S` 実行時に、ServerName未設定による以下の警告を確認しました。

```text
AH00558: Could not reliably determine the server's fully qualified domain name
```

本ハンズオンでは動作に影響しないため、現状のままとしています。
