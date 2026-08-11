<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$export_parameters = array();
if ($search !== '') {
    $export_parameters['q'] = $search;
}
if ($warehouse_id > 0) {
    $export_parameters['warehouse_id'] = (int) $warehouse_id;
}
$export_url = site_url('reports/low-stock/csv').(empty($export_parameters) ? '' : '?'.http_build_query($export_parameters, '', '&', PHP_QUERY_RFC3986));
?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
            <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Operations</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-600">Reports</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Low-stock report</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Prioritize replenishment where on-hand quantity is at or below the product alert level.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="<?php echo html_escape($export_url); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Export CSV</a>
        <a href="<?php echo site_url('stock'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Open inventory</a>
    </div>
</div>

<section class="mb-6 grid gap-4 sm:grid-cols-3" aria-label="Low-stock summary">
    <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-amber-700">Low-stock positions</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-amber-950"><?php echo html_escape(number_format($summary['position_count'])); ?></p>
        <p class="mt-3 text-xs text-amber-700">Matching the current filters</p>
    </article>
    <article class="rounded-2xl border border-red-200 bg-red-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-red-700">Total shortage</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-red-950"><?php echo html_escape(number_format($summary['total_shortage'])); ?></p>
        <p class="mt-3 text-xs text-red-700">Units needed to reach alert levels</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Warehouses affected</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['warehouse_count'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Within your authorized scope</p>
    </article>
</section>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 p-4 sm:p-5">
        <form method="get" action="<?php echo site_url('reports/low-stock'); ?>" class="grid gap-3 lg:grid-cols-[minmax(240px,1fr)_minmax(210px,260px)_auto] lg:items-end">
            <div>
                <label for="report-search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search products</label>
                <div class="relative">
                    <svg aria-hidden="true" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3.5-3.5" /></svg>
                    <input id="report-search" type="search" name="q" value="<?php echo html_escape($search); ?>" maxlength="200" placeholder="Search by product name or code" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                </div>
            </div>
            <div>
                <label for="report-warehouse" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Warehouse</label>
                <select id="report-warehouse" name="warehouse_id" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    <?php if ($is_admin): ?><option value="">All warehouses</option><?php endif; ?>
                    <?php foreach ($warehouses as $warehouse): ?>
                        <option value="<?php echo html_escape($warehouse->id); ?>" <?php echo (int) $warehouse_id === (int) $warehouse->id ? 'selected' : ''; ?>><?php echo html_escape($warehouse->name.' ('.$warehouse->code.')'.($warehouse->is_active ? '' : ' — Disabled')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-600 focus:ring-offset-2 lg:flex-none">Apply filters</button>
                <?php if ($search !== '' || ($is_admin && $warehouse_id > 0)): ?>
                    <a href="<?php echo site_url('reports/low-stock'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <p>Showing <span class="font-semibold text-slate-700"><?php echo html_escape($result_from); ?></span>–<span class="font-semibold text-slate-700"><?php echo html_escape($result_to); ?></span> of <span class="font-semibold text-slate-700"><?php echo html_escape($total_rows); ?></span> low-stock positions</p>
        <p>Rule: <span class="font-semibold text-slate-700">quantity ≤ alert quantity</span></p>
    </div>

    <?php if (!empty($rows)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Product</th>
                        <th scope="col" class="px-5 py-3">Warehouse</th>
                        <th scope="col" class="px-5 py-3 text-right">Quantity</th>
                        <th scope="col" class="px-5 py-3 text-right">Alert qty.</th>
                        <th scope="col" class="px-5 py-3 text-right">Shortage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($rows as $row): ?>
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo site_url('products/edit/'.$row->product_id); ?>" class="font-semibold text-slate-900 hover:text-brand-700"><?php echo html_escape($row->product_name); ?></a>
                                    <?php if (!$row->is_active): ?><span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">Disabled</span><?php endif; ?>
                                </div>
                                <p class="mt-1 font-mono text-xs text-slate-400"><?php echo html_escape($row->product_code); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-medium text-slate-700"><?php echo html_escape($row->warehouse_name); ?></p>
                                <p class="mt-1 font-mono text-xs text-slate-400"><?php echo html_escape($row->warehouse_code); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-base font-bold tabular-nums text-slate-900"><?php echo html_escape(number_format((int) $row->quantity)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right tabular-nums text-slate-500"><?php echo html_escape(number_format((int) $row->alert_quantity)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <span class="inline-flex min-w-10 justify-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold tabular-nums text-red-700"><?php echo html_escape(number_format((int) $row->shortage)); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="px-6 py-16 text-center">
            <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                <svg aria-hidden="true" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
            </span>
            <h2 class="mt-4 font-bold text-slate-900">No low-stock positions found</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">No inventory positions match the current warehouse and product filters.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($pagination)): ?>
        <div class="border-t border-slate-200 px-4 py-4 sm:px-5">
            <nav aria-label="Low-stock report pagination">
                <div class="flex flex-wrap items-center gap-1">
                    <?php foreach ($pagination as $item): ?>
                        <?php if ($item['type'] === 'ellipsis'): ?>
                            <span class="inline-flex min-h-9 min-w-9 items-center justify-center px-2 text-sm text-slate-400" aria-hidden="true">…</span>
                        <?php elseif ($item['current']): ?>
                            <span aria-current="page" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg bg-brand-600 px-3 text-sm font-semibold text-white"><?php echo html_escape($item['label']); ?></span>
                        <?php else: ?>
                            <a href="<?php echo html_escape($item['url']); ?>" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"><?php echo html_escape($item['label']); ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </nav>
        </div>
    <?php endif; ?>
</section>
