<?php
switch($_SERVER['SERVER_NAME']) {
    case 'localhost':
        define('MYSQL_HOSTNAME', "localhost");
        define('MYSQL_USERNAME', "root");
        define('MYSQL_PASSWORD', "");
        define('MYSQL_DATABASE', "amarokdb");
        define('MYSQL_CHARSET','utf-8');
        break;
    case 'mailcenter.caaciv.com':
        define('MYSQL_HOSTNAME', "caacivcom.ipagemysql.com");
        define('MYSQL_USERNAME', "correocaaciv");
        define('MYSQL_PASSWORD', "correos");
        define('MYSQL_DATABASE', "correo");
        define('MYSQL_CHARSET','utf-8');
        break;
    default:
        die("INVALID HOST");
}
?>