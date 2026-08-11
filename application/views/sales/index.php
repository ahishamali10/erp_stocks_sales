<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
            <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Operations</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-600">Sales</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Sales invoices</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Review finalized invoices and the warehouse stock movements they represent.</p>
    </div>
    <a href="<?php echo site_url('sales/create'); ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        <span aria-hidden="true" class="text-lg leading-none">+</span> New invoice
    </a>
</div>

<section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Sales summary">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Invoices</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['invoice_count'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Matching the current filters</p>
    </article>
    <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-emerald-700">Net sales</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-emerald-950">$<?php echo html_escape(number_format((float) $summary['sales_total'], 2)); ?></p>
        <p class="mt-3 text-xs text-emerald-700">After invoice discounts</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Discounts</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950">$<?php echo html_escape(number_format((float) $summary['discount_total'], 2)); ?></p>
        <p class="mt-3 text-xs text-slate-500">Granted across matching invoices</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Units sold</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['unit_count'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Total line quantities</p>
    </article>
</section>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 p-4 sm:p-5">
        <form method="get" action="<?php echo site_url('sales'); ?>" class="grid gap-3 lg:grid-cols-[minmax(240px,1fr)_minmax(210px,260px)_auto] lg:items-end">
            <div>
                <label for="sales-search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search invoices</label>
                <input id="sales-search" type="search" name="q" value="<?php echo html_escape($search); ?>" maxlength="200" placeholder="Invoice number or customer" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            </div>
            <div>
                <label for="sales-warehouse" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Warehouse</label>
                <select id="sales-warehouse" name="warehouse_id" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    <?php if ($is_admin): ?><option value="">All warehouses</option><?php endif; ?>
                    <?php foreach ($warehouses as $warehouse): ?>
                        <option value="<?php echo html_escape($warehouse->id); ?>" <?php echo (int) $warehouse_id === (int) $warehouse->id ? 'selected' : ''; ?>><?php echo html_escape($warehouse->name.' ('.$warehouse->code.')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-600 focus:ring-offset-2 lg:flex-none">Apply filters</button>
                <?php if ($search !== '' || ($is_admin && $warehouse_id > 0)): ?>
                    <a href="<?php echo site_url('sales'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="border-b border-slate-200 px-4 py-3 text-xs text-slate-500 sm:px-5">Showing <span class="font-semibold text-slate-700"><?php echo html_escape($result_from); ?></span>–<span class="font-semibold text-slate-700"><?php echo html_escape($result_to); ?></span> of <span class="font-semibold text-slate-700"><?php echo html_escape($total_rows); ?></span> invoices</div>

    <?php if (!empty($sales)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr><th class="px-5 py-3">Invoice</th><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Warehouse</th><th class="px-5 py-3">Issued by</th><th class="px-5 py-3 text-right">Items</th><th class="px-5 py-3 text-right">Total</th><th class="px-5 py-3"><span class="sr-only">Action</span></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($sales as $invoice): ?>
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-5 py-4"><a href="<?php echo site_url('sales/view/'.$invoice->id); ?>" class="font-semibold text-brand-700 hover:text-brand-900"><?php echo html_escape($invoice->invoice_number); ?></a><p class="mt-1 text-xs text-slate-400"><?php echo html_escape(date('M j, Y · H:i', strtotime($invoice->created_at))); ?></p></td>
                            <td class="px-5 py-4 font-medium text-slate-800"><?php echo html_escape($invoice->customer_name); ?></td>
                            <td class="whitespace-nowrap px-5 py-4"><p class="font-medium text-slate-700"><?php echo html_escape($invoice->warehouse_name); ?></p><p class="mt-1 font-mono text-xs text-slate-400"><?php echo html_escape($invoice->warehouse_code); ?></p></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo html_escape($invoice->user_name ?: 'System / legacy'); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right"><p class="font-semibold tabular-nums text-slate-900"><?php echo html_escape(number_format((int) $invoice->total_quantity)); ?> units</p><p class="mt-1 text-xs text-slate-400"><?php echo html_escape(number_format((int) $invoice->line_count)); ?> lines</p></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-base font-bold tabular-nums text-slate-950">$<?php echo html_escape(number_format((float) $invoice->total, 2)); ?></td>
                            <td class="px-5 py-4 text-right"><a href="<?php echo site_url('sales/view/'.$invoice->id); ?>" class="inline-flex min-h-9 items-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 hover:border-brand-300 hover:text-brand-700">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="px-6 py-16 text-center"><h2 class="font-bold text-slate-900">No invoices found</h2><p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">No finalized invoices match the current warehouse and search filters.</p><a href="<?php echo site_url('sales/create'); ?>" class="mt-5 inline-flex min-h-10 items-center rounded-xl bg-brand-600 px-4 text-sm font-semibold text-white">Create invoice</a></div>
    <?php endif; ?>

    <?php if (!empty($pagination)): ?>
        <div class="border-t border-slate-200 px-4 py-4 sm:px-5"><nav aria-label="Invoice pagination"><div class="flex flex-wrap items-center gap-1">
            <?php foreach ($pagination as $item): ?>
                <?php if ($item['type'] === 'ellipsis'): ?><span class="inline-flex min-h-9 min-w-9 items-center justify-center text-slate-400">…</span>
                <?php elseif ($item['current']): ?><span aria-current="page" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg bg-brand-600 px-3 text-sm font-semibold text-white"><?php echo html_escape($item['label']); ?></span>
                <?php else: ?><a href="<?php echo html_escape($item['url']); ?>" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:border-brand-300 hover:text-brand-700"><?php echo html_escape($item['label']); ?></a><?php endif; ?>
            <?php endforeach; ?>
        </div></nav></div>
    <?php endif; ?>
</section>
