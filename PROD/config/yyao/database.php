<?php
$default_fetch_mode = PDO::FETCH_ASSOC;

$config['master'] = array(
    'dsn'=>'mysql:host=127.0.0.1;dbname=youyao_db',
    'username' => 'yyfast01',
    'password' => 'ufxjp3x#z&',
    'init_attributes' => array(),
    'init_statements' => array(
        'SET CHARACTER SET utf8mb4',
        'SET NAMES utf8mb4'
    ),
    'default_fetch_mode' => $default_fetch_mode
);

$config['slave'] = array(
    'dsn'=>'mysql:host=127.0.0.1;dbname=youyao_db',
    'username' => 'yyfast01',
    'password' => 'ufxjp3x#z&',
    'init_attributes' => array(),
    'init_statements' => array(
        'SET CHARACTER SET utf8mb4',
        'SET NAMES utf8mb4'
    ),
    'default_fetch_mode' => $default_fetch_mode
);


$config['ablog_master'] = array(
    'dsn'=>'mysql:host=127.0.0.1;dbname=ablog_db',
    'username' => 'yyfast01',
    'password' => 'ufxjp3x#z&',
    'init_attributes' => array(),
    'init_statements' => array(
        'SET CHARACTER SET utf8mb4',
        'SET NAMES utf8mb4'
    ),
    'default_fetch_mode' => $default_fetch_mode
);

$config['ablog_slave'] = array(
    'dsn'=>'mysql:host=127.0.0.1;dbname=ablog_db',
    'username' => 'yyfast01',
    'password' => 'ufxjp3x#z&',
    'init_attributes' => array(),
    'init_statements' => array(
        'SET CHARACTER SET utf8mb4',
        'SET NAMES utf8mb4'
    ),
    'default_fetch_mode' => $default_fetch_mode
);


