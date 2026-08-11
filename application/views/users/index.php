<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
            <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Administration</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-600">Users</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">User management</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Control ERP accounts, administrator access, and warehouse assignments.</p>
    </div>
    <a href="<?php echo site_url('users/create'); ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
        Add user
    </a>
</div>

<section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="User summary">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Total users</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['total_count'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Configured ERP accounts</p>
    </article>
    <article class="rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-violet-700">Administrators</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-violet-950"><?php echo html_escape(number_format($summary['admin_count'])); ?></p>
        <p class="mt-3 text-xs text-violet-700">Organization-wide access</p>
    </article>
    <article class="rounded-2xl border border-brand-200 bg-brand-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-brand-700">Warehouse users</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-brand-950"><?php echo html_escape(number_format($summary['warehouse_user_count'])); ?></p>
        <p class="mt-3 text-xs text-brand-700">Restricted operational access</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Assigned warehouses</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['assigned_warehouse_count'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Locations with user access</p>
    </article>
</section>

<section class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <form method="get" action="<?php echo site_url('users'); ?>" class="grid gap-3 lg:grid-cols-[minmax(240px,1fr)_minmax(200px,240px)_auto] lg:items-end">
        <div>
            <label for="user-search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search users</label>
            <div class="relative">
                <svg aria-hidden="true" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3.5-3.5" /></svg>
                <input id="user-search" type="search" name="q" value="<?php echo html_escape($search); ?>" maxlength="200" placeholder="Name or email" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            </div>
        </div>
        <div>
            <label for="user-role-filter" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Role</label>
            <select id="user-role-filter" name="role" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                <option value="">All roles</option>
                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                <option value="user_warehouse" <?php echo $role === 'user_warehouse' ? 'selected' : ''; ?>>Warehouse user</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 lg:flex-none">Apply filters</button>
            <?php if ($search !== '' || $role !== ''): ?><a href="<?php echo site_url('users'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Clear</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-bold text-slate-900">Access directory</h2>
            <p class="mt-1 text-xs text-slate-500"><?php echo html_escape(number_format($total_rows)); ?> matching account<?php echo $total_rows === 1 ? '' : 's'; ?></p>
        </div>
        <?php if ($total_rows > 0): ?><p class="text-xs text-slate-500">Showing <?php echo html_escape($result_from); ?>–<?php echo html_escape($result_to); ?></p><?php endif; ?>
    </div>

    <?php if (!empty($users)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">User</th>
                        <th scope="col" class="px-5 py-3">Role</th>
                        <th scope="col" class="px-5 py-3">Warehouse scope</th>
                        <th scope="col" class="px-5 py-3 text-right">Invoices</th>
                        <th scope="col" class="px-5 py-3">Created</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($users as $user): ?>
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo site_url('users/edit/'.$user->id); ?>" class="font-semibold text-slate-900 hover:text-brand-700"><?php echo html_escape($user->name); ?></a>
                                    <?php if ((int) $user->id === (int) $this->current_user['id']): ?><span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700">You</span><?php endif; ?>
                                </div>
                                <p class="mt-1 text-xs text-slate-500"><?php echo html_escape($user->email); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <?php if ($user->role === 'admin'): ?>
                                    <span class="inline-flex rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">Administrator</span>
                                <?php else: ?>
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">Warehouse user</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4">
                                <?php if ($user->role === 'admin'): ?>
                                    <p class="font-medium text-slate-700">All warehouses</p>
                                    <p class="mt-1 text-xs text-slate-400">Organization-wide</p>
                                <?php else: ?>
                                    <p class="font-medium text-slate-700"><?php echo html_escape($user->warehouse_name ?: 'Unassigned'); ?></p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <?php if ($user->warehouse_code): ?><p class="font-mono text-xs text-slate-400"><?php echo html_escape($user->warehouse_code); ?></p><?php endif; ?>
                                        <?php if ($user->warehouse_is_active !== NULL && !$user->warehouse_is_active): ?><span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">Disabled</span><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold tabular-nums text-slate-700"><?php echo html_escape(number_format((int) $user->sale_count)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-500"><?php echo html_escape(date('M j, Y', strtotime($user->created_at))); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo site_url('users/edit/'.$user->id); ?>" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Edit</a>
                                    <?php if ((int) $user->id === (int) $this->current_user['id']): ?>
                                        <span class="inline-flex min-h-9 items-center justify-center rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-400" title="You cannot delete your signed-in account">Signed in</span>
                                    <?php elseif ((int) $user->sale_count > 0): ?>
                                        <span class="inline-flex min-h-9 items-center justify-center rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-400" title="Users with attributed invoices cannot be deleted">In use</span>
                                    <?php elseif ($user->role === 'admin' && (int) $summary['admin_count'] <= 1): ?>
                                        <span class="inline-flex min-h-9 items-center justify-center rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-400" title="The final administrator cannot be deleted">Required</span>
                                    <?php else: ?>
                                        <?php echo form_open('users/delete/'.$user->id, array('data-confirm' => 'Delete user '.$user->name.'? This cannot be undone.', 'class' => 'inline')); ?>
                                            <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Delete</button>
                                        <?php echo form_close(); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="px-6 py-16 text-center">
            <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500"><svg aria-hidden="true" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-4h6m-3-3v6" /></svg></span>
            <h2 class="mt-4 font-bold text-slate-900">No users found</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">Adjust the filters or create a new ERP account.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($pagination)): ?>
        <div class="border-t border-slate-200 px-4 py-4 sm:px-5">
            <nav aria-label="User pagination"><div class="flex flex-wrap items-center gap-1">
                <?php foreach ($pagination as $item): ?>
                    <?php if ($item['type'] === 'ellipsis'): ?><span class="inline-flex min-h-9 min-w-9 items-center justify-center px-2 text-sm text-slate-400" aria-hidden="true">…</span>
                    <?php elseif ($item['current']): ?><span aria-current="page" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg bg-brand-600 px-3 text-sm font-semibold text-white"><?php echo html_escape($item['label']); ?></span>
                    <?php else: ?><a href="<?php echo html_escape($item['url']); ?>" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"><?php echo html_escape($item['label']); ?></a><?php endif; ?>
                <?php endforeach; ?>
            </div></nav>
        </div>
    <?php endif; ?>
</section>
