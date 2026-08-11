<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape(isset($page_title) ? $page_title.' | Sales & Stock ERP Mini' : 'Sales & Stock ERP Mini'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="<?php echo site_url(); ?>">Sales &amp; Stock ERP Mini</a>
            <nav aria-label="Primary navigation">
                <a href="<?php echo site_url(); ?>">Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="container page-content">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success" role="status"><?php echo html_escape($this->session->flashdata('success')); ?></div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-error" role="alert"><?php echo html_escape($this->session->flashdata('error')); ?></div>
        <?php endif; ?>
