<?php
defined('BASEPATH') or exit('No direct script access allowed');

$active_group  = 'default';
$query_builder = true;

$db['default'] = [
    'dsn'          => '',
    'hostname'     => 'localhost',
    'username'     => 'sbcdev_user',
    'password'     => 'Sbcdev@Sql123',
    'database'     => 'cyspharma_db',
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
// 'username'     => 'sbcdev_user',
// 'password'     => 'Sbcdev@Sql123',