<?php

require '../genesis/autoload.php';

startSession();

$app = new Router;



$app->get('/', ['Product', 'list']);

$app->get('/product/:id', ['Product', 'show']);

$app->post('/product/:id', ['Product', 'add']);



$app->get('/account', ['User', 'account']);

$app->get('/pay', ['User', 'pay']);

$app->get('/account/:id', ['User', 'remove']);

$app->get('/login', ['User', 'login_get']);

$app->post('/login', ['User', 'login_post']);

$app->get('/register', ['User', 'register_get']);

$app->post('/register', ['User', 'register_post']);

$app->get('/logout', ['User', 'logout']);



$app->server();