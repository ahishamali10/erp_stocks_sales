<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?php echo html_escape($page_title.' | Sales & Stock ERP Mini'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-900 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-7 text-center text-white">
                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-brand-500 shadow-lg shadow-brand-950/50">
                    <svg aria-hidden="true" class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8-4 8 4-8 4-8-4Zm0 5 8 4 8-4M4 17l8 4 8-4" /></svg>
                </span>
                <h1 class="mt-4 text-2xl font-bold tracking-tight">StockFlow ERP</h1>
                <p class="mt-1 text-sm text-slate-400">Sign in to the sales and inventory workspace</p>
            </div>

            <section class="rounded-2xl border border-white/10 bg-white p-6 shadow-2xl sm:p-8">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><?php echo html_escape($this->session->flashdata('error')); ?></div>
                <?php endif; ?>
                <?php if ($error !== ''): ?>
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><?php echo html_escape($error); ?></div>
                <?php endif; ?>

                <?php echo form_open('login', array('class' => 'space-y-5')); ?>
                    <div>
                        <label for="login-email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email address</label>
                        <input id="login-email" type="email" name="email" value="<?php echo html_escape(set_value('email', '', FALSE)); ?>" maxlength="150" required autofocus autocomplete="username" placeholder="you@example.com" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div>
                        <label for="login-password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password</label>
                        <input id="login-password" type="password" name="password" required autocomplete="current-password" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Sign in</button>
                <?php echo form_close(); ?>

                <p class="mt-6 text-center text-xs leading-5 text-slate-500">Use the local demonstration credentials documented in README.</p>
            </section>
        </div>
    </main>
</body>
</html>
