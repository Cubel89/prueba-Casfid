<?php
session_start();
require_once 'config.php';


session_destroy();


header('Location: login.php?success=Has cerrado sesión correctamente');
exit;