<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$page_title = isset($page_title) ? $page_title : 'Dashboard';
$page_description = isset($page_description) ? $page_description : '';
$active_nav = isset($active_nav) ? $active_nav : '';
$user_name = isset($this->current_user['name']) ? $this->current_user['name'] : 'ERP user';
$name_parts = preg_split('/\s+/', trim($user_name));
$initials = '';
foreach (array_slice($name_parts, 0, 2) as $name_part) {
    $initials .= strtoupper(substr($name_part, 0, 1));
}
$initials = $initials !== '' ? $initials : 'EU';
$scope_label = isset($this->current_user['role']) && $this->current_user['role'] === 'admin'
    ? 'All warehouses'
    : (isset($this->current_user['warehouse_name']) ? $this->current_user['warehouse_name'] : 'Assigned warehouse');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?php echo html_escape($page_title.' | Sales & Stock ERP Mini'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
    <script defer src="<?php echo base_url('assets/js/app.js'); ?>"></script>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="min-h-screen">
        <div data-sidebar-overlay data-sidebar-close class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

        <?php $this->load->view('layouts/sidebar', array('active_nav' => $active_nav)); ?>

        <div class="min-h-screen lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" data-sidebar-toggle class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 lg:hidden" aria-label="Open navigation">
                            <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold uppercase tracking-wider text-slate-400">Operations workspace</p>
                            <p class="truncate text-sm font-semibold text-slate-800"><?php echo html_escape($page_title); ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold text-slate-800"><?php echo html_escape($user_name); ?></p>
                            <p class="text-xs text-slate-500"><?php echo html_escape($scope_label); ?></p>
                        </div>
                        <div class="flex size-10 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700" aria-hidden="true"><?php echo html_escape($initials); ?></div>
                        <?php echo form_open('logout'); ?>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" aria-label="Sign out">
                                <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m10-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" /></svg>
                                <span class="hidden sm:inline">Sign out</span>
                            </button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <div class="mx-auto max-w-7xl">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm" role="status">
                            <svg aria-hidden="true" class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                            <p><?php echo html_escape($this->session->flashdata('success')); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" role="alert">
                            <svg aria-hidden="true" class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.5 17.4A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.6L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                            </svg>
                            <p><?php echo html_escape($this->session->flashdata('error')); ?></p>
                        </div>
                    <?php endif; ?>
