<?php
$pdo = new PDO("mysql:host=localhost;dbname=projetinfra1;charset=utf8", "projetuser", "motdepassefort");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
