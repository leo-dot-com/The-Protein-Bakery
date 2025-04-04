<?php
// /the-protein-bakery/includes/auth_functions.php

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /the-protein-bakery/auth/login.php");
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}