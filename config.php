<?php
/**
 * config.php
 *
 * This file contains configuration for the application
 */

// Database configuration
$db_host = isset($_SERVER['OPENSHIFT_MYSQL_DB_HOST']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_HOST'] : 'localhost';
$db_user = isset($_SERVER['OPENSHIFT_MYSQL_DB_USERNAME']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_USERNAME'] : 'root';
$db_pass = isset($_SERVER['OPENSHIFT_MYSQL_DB_PASSWORD']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_PASSWORD'] : '';
$db_name = isset($_SERVER['OPENSHIFT_APP_NAME']) ?  $_SERVER['OPENSHIFT_APP_NAME'] : 'dc';
$db_port = isset($_SERVER['OPENSHIFT_MYSQL_DB_PORT']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_PORT'] : 3306;

// SMTP Configuration
$smtp_host = ' smtp.mail.yahoo.com';
$smtp_auth = true;
$smtp_username = 'droidcare@yahoo.com';
$smtp_password = 'eracdiord1234';
$smtp_secure = 'ssl'; 
$smtp_port = 465;
$smtp_from = 'droidcare@yahoo.com';
$smtp_from_name = 'DroidCare';