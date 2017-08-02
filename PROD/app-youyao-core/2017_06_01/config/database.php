<?php
$default_fetch_mode = PDO::FETCH_ASSOC;
    $config['master'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=one_db;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode
    );

    $config['slave'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=one_db;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode
    );

    $config['lkymaster'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=LKYou;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode
    );

    $config['lkyslave'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=LKYou;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode
    );

    $config['mkmaster'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=marketing;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode
    );

    $config['mkslave'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=marketing;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode
    );
    $config['statsmaster'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=stats_db;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode,
        'driver_options' => array(
            PDO::MYSQL_ATTR_LOCAL_INFILE => true
        )
    );
    $config['bbs'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=bbs_db;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode
    );

    $config['blog'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=ablog_db;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode
    );

    $config['dwmaster'] = array(
        'dsn' => 'mysql:host=192.168.8.8;dbname=dw_db;port=3306',
        'username' => 'zzkdbuser',
        'password' => 'gwutest',
        'init_attributes' => array(),
        'init_statements' => array(
            'SET CHARACTER SET utf8',
            'SET NAMES utf8'
        ),
        'default_fetch_mode' => $default_fetch_mode
    );
