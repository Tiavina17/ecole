<?php
require 'config.php';
session_destroy();      // supprime TOUTES les données de session
header('Location: login.php');
exit;