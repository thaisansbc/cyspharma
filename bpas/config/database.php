<?php
defined('BASEPATH') or exit('No direct script access allowed');

$active_group  = 'default';
$query_builder = true;

$db['default'] = [
    'dsn'          => '',
    'hostname'     => 'localhost',
<<<<<<< HEAD
    'username'     => 'sbcdev_user',
    'password'     => 'Sbcdev@Sql123',
    'database'     => 'cyspharma_db',
=======
    'username'     => 'root',
    'username'     => 'sbcdev_user',
    'password'     => 'Sbcdev@Sql123',
>>>>>>> af3180b8fe1099c1111b26ef7dba6b7b9a7d6099
    'dbdriver'     => 'mysqli',
    'dbprefix'     => 'bpas_',
    'pconnect'     => false,
    'db_debug'     => true,
    'cache_on'     => false,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => false,
    'compress'     => false,
    'stricton'     => false,
    'failover'     => [],
    'save_queries' => false,
];