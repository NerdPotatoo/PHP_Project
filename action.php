<?php

require_once 'vendor/autoload.php';

if(isset($_GET['page'])) {
    if($_GET['page'] == 'home') {
        include 'pages/index.php';
    }
    else if($_GET['page'] == 'about') {
        include 'pages/about.php';
    } 
    else if($_GET['page'] == 'products') {
        include 'pages/products.php';
    } 
    else if($_GET['page'] == 'login') {
        include 'pages/login.php';
    } 
    else if($_GET['page'] == 'signup') {
        include 'pages/signup.php';
    } 
    else if($_GET['page'] == 'contact') {
        include 'pages/contact.php';
    } 
    else if($_GET['page'] == 'cart') {
        include 'pages/cart.php';
    } 
    else if($_GET['page'] == 'admin/dashboard') {
        include 'pages/admin/dashboard.php';
    }
    else if($_GET['page'] == 'admin/products') {
        include 'pages/admin/products.php';
    }
    else if($_GET['page'] == 'admin/add-product') {
        include 'pages/admin/add-product.php';
    }
    else if($_GET['page'] == 'admin/orders') {
        include 'pages/admin/orders.php';
    }
    else if($_GET['page'] == 'admin/customers') {
        include 'pages/admin/customers.php';
    }
    else if($_GET['page'] == 'admin/contacts') {
        include 'pages/admin/contacts.php';
    }
    else if($_GET['page'] == 'admin/login') {
        include 'pages/admin/login.php';
    }
    else if($_GET['page'] == 'admin/setup') {
        include 'pages/admin/setup.php';
    }
    else if($_GET['page'] == 'logout') {
        include 'pages/logout.php';
    }
    else {
        // Default to home page if route not found
        include 'pages/index.php';
    }
} else {
    // If no page parameter, show home page
    include 'pages/index.php';
}
