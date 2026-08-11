<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$name_value = set_value('name', $user ? $user->name : '', FALSE);
$email_value = set_value('email', $user ? $user->email : '', FALSE);
$role_value = set_value('role', $user ? $user->role : 'user_warehouse', FALSE);
$warehouse_value = set_value('warehouse_id', $user && $user->warehouse_id ? $user->warehouse_id : '', FALSE);
$name_error = trim(strip_tags(form_error('name')));
$email_error = trim(strip_tags(form_error('email')));
$role_error = trim(strip_tags(form_error('role')));
$warehouse_error = trim(strip_tags(form_error('warehouse_id')));
$password_error = trim(strip_tags(form_error('password')));
$confirmation_error = trim(strip_tags(form_error('password_confirmation')));
?>
<div class="mb-6">
    <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
        <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Administration</a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo site_url('users'); ?>" class="hover:text-brand-700">Users</a>
        <span aria-hidden="true">/</span>
        <span class="text-slate-600"><?php echo $user ? 'Edit' : 'Add'; ?></span>
    </nav>
    <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl"><?php echo html_escape($page_title); ?></h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"><?php echo html_escape($page_description); ?></p>
</div>

<?php if ($save_error !== ''): ?><div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><?php echo html_escape($save_error); ?></div><?php endif; ?>

<div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="font-bold text-slate-900">Account and access</h2>
            <p class="mt-1 text-xs text-slate-500">Passwords are hashed before storage and are never displayed again.</p>
        </div>
        <?php echo form_open($form_action, array('class' => 'p-5 sm:p-6')); ?>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="user-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Full name <span class="text-red-500" aria-hidden="true">*</span></label>
                    <input id="user-name" type="text" name="name" value="<?php echo html_escape($name_value); ?>" maxlength="150" required autofocus autocomplete="name" placeholder="e.g. Operations Manager" class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 <?php echo $name_error ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                    <?php if ($name_error): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($name_error); ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="user-email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email address <span class="text-red-500" aria-hidden="true">*</span></label>
                    <input id="user-email" type="email" name="email" value="<?php echo html_escape($email_value); ?>" maxlength="150" required autocomplete="username" placeholder="e.g. operator@example.com" class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 <?php echo $email_error ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                    <?php if ($email_error): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($email_error); ?></p><?php endif; ?>
                </div>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="user-role" class="mb-1.5 block text-sm font-semibold text-slate-700">Role <span class="text-red-500" aria-hidden="true">*</span></label>
                    <select id="user-role" name="role" required class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:ring-4 <?php echo $role_error ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                        <option value="admin" <?php echo $role_value === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                        <option value="user_warehouse" <?php echo $role_value === 'user_warehouse' ? 'selected' : ''; ?>>Warehouse user</option>
                    </select>
                    <?php if ($role_error): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($role_error); ?></p><?php endif; ?>
                    <p class="mt-1.5 text-xs text-slate-500">Administrators can manage all warehouses and user accounts.</p>
                </div>
                <div data-warehouse-assignment>
                    <label for="user-warehouse" class="mb-1.5 block text-sm font-semibold text-slate-700">Assigned warehouse <span class="text-red-500" aria-hidden="true">*</span></label>
                    <select id="user-warehouse" name="warehouse_id" class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:ring-4 <?php echo $warehouse_error ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                        <option value="">Select a warehouse</option>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <option value="<?php echo html_escape($warehouse->id); ?>" <?php echo (int) $warehouse_value === (int) $warehouse->id ? 'selected' : ''; ?> <?php echo !$warehouse->is_active ? 'disabled' : ''; ?>><?php echo html_escape($warehouse->name.' ('.$warehouse->code.')'.($warehouse->is_active ? '' : ' — Disabled')); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($warehouse_error): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($warehouse_error); ?></p><?php endif; ?>
                    <p class="mt-1.5 text-xs text-slate-500">Warehouse users can access only this location.</p>
                </div>
            </div>

            <div class="mt-7 border-t border-slate-200 pt-6">
                <div class="mb-4">
                    <h3 class="font-bold text-slate-900"><?php echo $user ? 'Change password' : 'Set password'; ?></h3>
                    <p class="mt-1 text-xs text-slate-500"><?php echo $user ? 'Leave both fields blank to keep the existing password.' : 'Use between 8 and 72 characters.'; ?></p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="user-password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password <?php if (!$user): ?><span class="text-red-500" aria-hidden="true">*</span><?php endif; ?></label>
                        <input id="user-password" type="password" name="password" minlength="8" maxlength="72" <?php echo $user ? '' : 'required'; ?> autocomplete="new-password" class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:ring-4 <?php echo $password_error ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                        <?php if ($password_error): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($password_error); ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="user-password-confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Confirm password <?php if (!$user): ?><span class="text-red-500" aria-hidden="true">*</span><?php endif; ?></label>
                        <input id="user-password-confirmation" type="password" name="password_confirmation" minlength="8" maxlength="72" <?php echo $user ? '' : 'required'; ?> autocomplete="new-password" class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:ring-4 <?php echo $confirmation_error ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                        <?php if ($confirmation_error): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($confirmation_error); ?></p><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="<?php echo site_url('users'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Cancel</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9 16l10-10" /></svg>
                    <?php echo html_escape($submit_label); ?>
                </button>
            </div>
        <?php echo form_close(); ?>
    </section>

    <aside class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold text-slate-900">Access rules</h2>
            <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>Administrators can access every warehouse and manage users.</span></li>
                <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>Warehouse users are restricted server-side to one active warehouse.</span></li>
                <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>The final administrator cannot be demoted or deleted.</span></li>
                <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>Accounts with attributed invoices are preserved for audit history.</span></li>
            </ul>
        </div>
        <?php if ($user): ?>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Account record</p>
                <p class="mt-3 text-sm font-semibold text-slate-700">Created <?php echo html_escape(date('M j, Y', strtotime($user->created_at))); ?></p>
                <?php if ((int) $user->id === (int) $this->current_user['id']): ?><p class="mt-2 text-xs leading-5 text-amber-700">This is your signed-in account. It cannot be deleted.</p><?php endif; ?>
            </div>
        <?php endif; ?>
    </aside>
</div>

<script>
(function () {
    'use strict';
    var role = document.getElementById('user-role');
    var warehouse = document.getElementById('user-warehouse');
    var assignment = document.querySelector('[data-warehouse-assignment]');

    if (!role || !warehouse || !assignment) {
        return;
    }

    function syncWarehouse() {
        var required = role.value === 'user_warehouse';
        warehouse.disabled = !required;
        warehouse.required = required;
        assignment.classList.toggle('opacity-50', !required);

        if (!required) {
            warehouse.value = '';
        }
    }

    role.addEventListener('change', syncWarehouse);
    syncWarehouse();
}());
</script>
