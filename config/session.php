<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function isEmployer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employer';
}

function isJobSeeker() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'job_seeker';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['name'] ?? '';
}

function getUserRole() {
    return $_SESSION['role'] ?? '';
}
?>