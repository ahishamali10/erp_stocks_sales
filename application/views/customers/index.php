<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
            <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Operations</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-600">Customers</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Customers</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Maintain invoice contacts and preserve their sales history.</p>
    </div>
    <a href="<?php echo site_url('customers/create'); ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
        Add customer
    </a>
</div>

<section class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <?php echo form_open('customers', array('method' => 'get', 'class' => 'flex flex-col gap-3 sm:flex-row sm:items-end')); ?>
        <div class="min-w-0 flex-1">
            <label for="customer-search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search customers</label>
            <div class="relative">
                <svg aria-hidden="true" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3.5-3.5" /></svg>
                <input id="customer-search" type="search" name="q" value="<?php echo html_escape($search); ?>" maxlength="200" placeholder="Name, phone, or email" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            </div>
        </div>
        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">Search</button>
        <?php if ($search !== ''): ?>
            <a href="<?php echo site_url('customers'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Clear</a>
        <?php endif; ?>
    <?php echo form_close(); ?>
</section>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-bold text-slate-900">Customer directory</h2>
            <p class="mt-1 text-xs text-slate-500"><?php echo html_escape(number_format($total_rows)); ?> matching record<?php echo $total_rows === 1 ? '' : 's'; ?></p>
        </div>
        <?php if ($total_rows > 0): ?><p class="text-xs text-slate-500">Showing <?php echo html_escape($result_from); ?>–<?php echo html_escape($result_to); ?></p><?php endif; ?>
    </div>

    <?php if (!empty($customers)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Customer</th>
                        <th scope="col" class="px-5 py-3">Contact</th>
                        <th scope="col" class="px-5 py-3 text-right">Invoices</th>
                        <th scope="col" class="px-5 py-3 text-right">Sales total</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($customers as $customer): ?>
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <a href="<?php echo site_url('customers/edit/'.$customer->id); ?>" class="font-semibold text-slate-900 hover:text-brand-700"><?php echo html_escape($customer->name); ?></a>
                                <p class="mt-1 text-xs text-slate-400">Customer #<?php echo html_escape($customer->id); ?></p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <?php if ($customer->email): ?><a href="mailto:<?php echo html_escape($customer->email); ?>" class="block hover:text-brand-700"><?php echo html_escape($customer->email); ?></a><?php endif; ?>
                                <?php if ($customer->phone): ?><span class="<?php echo $customer->email ? 'mt-1 block' : 'block'; ?> text-xs text-slate-500"><?php echo html_escape($customer->phone); ?></span><?php endif; ?>
                                <?php if (!$customer->email && !$customer->phone): ?><span class="text-xs text-slate-400">No contact details</span><?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right tabular-nums text-slate-600"><?php echo html_escape(number_format((int) $customer->sale_count)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold tabular-nums text-slate-800">$<?php echo html_escape(number_format((float) $customer->total_spent, 2)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo site_url('customers/edit/'.$customer->id); ?>" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Edit</a>
                                    <?php if ((int) $customer->sale_count > 0): ?>
                                        <button type="button" disabled title="Customers with invoices cannot be deleted" class="inline-flex min-h-9 cursor-not-allowed items-center justify-center rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-400">Delete</button>
                                    <?php else: ?>
                                        <?php echo form_open('customers/delete/'.$customer->id, array('class' => 'inline-flex', 'data-confirm' => html_escape('Delete '.$customer->name.'? This cannot be undone.'))); ?>
                                            <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-red-50 px-3 text-xs font-semibold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Delete</button>
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
            <h2 class="font-bold text-slate-900"><?php echo $search !== '' ? 'No customers match this search' : 'No customers yet'; ?></h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500"><?php echo $search !== '' ? 'Try another name, phone number, or email address.' : 'Create a customer before building a sales invoice.'; ?></p>
            <?php if ($search !== ''): ?><a href="<?php echo site_url('customers'); ?>" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Clear search</a><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($pagination)): ?>
        <nav class="flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 px-5 py-4" aria-label="Customer pagination">
            <?php foreach ($pagination as $item): ?>
                <?php if ($item['type'] === 'ellipsis'): ?>
                    <span class="px-1 text-sm text-slate-400">…</span>
                <?php else: ?>
                    <a href="<?php echo html_escape($item['url']); ?>" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-semibold transition <?php echo $item['current'] ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-300 hover:text-brand-700'; ?>" <?php echo $item['current'] ? 'aria-current="page"' : ''; ?>><?php echo html_escape($item['label']); ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</section>
