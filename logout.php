<?php
require_once 'init.php';

// Destrói a sessão
session_destroy();

// Redireciona para a página de login
header('Location: login.php');
exit; 