<?php

function getDb() {
    $db = new PDO('sqlite:' . __DIR__ . '/links.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}
