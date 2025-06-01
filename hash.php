<?php
$password = 'lyamboss';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash généré : " . $hash;
