<?php
$getRoutes = [
    '/' => function () {
        echo "hello"; 
    },
    'demo' => function () {
        echo "demo";
    },
    'test/{id}/{name}' => "test.php"
];
$postRoutes = [
    'demos' => function ($body) {
        echo "lol";
    }
];
