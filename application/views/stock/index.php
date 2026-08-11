<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
            <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Operations</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-600">Inventory</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Warehouse inventory</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Review on-hand quantities for every product and warehouse combination.</p>
    </div>
    <a href="<?php echo site_url('products'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Manage products</a>
</div>

<section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Inventory summary">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Warehouses in view</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['warehouse_count'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Physical stock locations</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Products in view</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['product_count'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Active and disabled catalog records</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Total units on hand</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['total_units'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Across the selected locations</p>
    </article>
    <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-amber-700">Low-stock positions</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-amber-950"><?php echo html_escape(number_format($summary['low_stock_count'])); ?></p>
        <p class="mt-3 text-xs text-amber-700">Quantity at or below its alert level</p>
    </article>
</section>

<section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 p-4 sm:p-5">
        <form method="get" action="<?php echo site_url('stock'); ?>" class="grid gap-3 lg:grid-cols-[minmax(240px,1fr)_minmax(210px,260px)_auto] lg:items-end">
            <div>
                <label for="stock-search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search inventory</label>
                <div class="relative">
                    <svg aria-hidden="true" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3.5-3.5" /></svg>
                    <input id="stock-search" type="search" name="q" value="<?php echo html_escape($search); ?>" maxlength="200" placeholder="Search by product name or code" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                </div>
            </div>
            <div>
                <label for="warehouse-filter" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Warehouse</label>
                <select id="warehouse-filter" name="warehouse_id" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    <?php if ($this->current_user['role'] === 'admin'): ?><option value="">All warehouses</option><?php endif; ?>
                    <?php foreach ($warehouses as $warehouse): ?>
                        <option value="<?php echo html_escape($warehouse->id); ?>" <?php echo (int) $warehouse_id === (int) $warehouse->id ? 'selected' : ''; ?>><?php echo html_escape($warehouse->name.' ('.$warehouse->code.')'.($warehouse->is_active ? '' : ' — Disabled')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-600 focus:ring-offset-2 lg:flex-none">Apply filters</button>
                <?php if ($search !== '' || $warehouse_id > 0): ?>
                    <a href="<?php echo site_url('stock'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <p>Showing <span class="font-semibold text-slate-700"><?php echo html_escape($result_from); ?></span>–<span class="font-semibold text-slate-700"><?php echo html_escape($result_to); ?></span> of <span class="font-semibold text-slate-700"><?php echo html_escape($total_rows); ?></span> inventory positions</p>
        <?php if ($search !== ''): ?><p>Search: <span class="font-semibold text-slate-700">“<?php echo html_escape($search); ?>”</span></p><?php endif; ?>
    </div>

    <?php if (!empty($inventory_rows)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Product</th>
                        <th scope="col" class="px-5 py-3">Warehouse</th>
                        <th scope="col" class="px-5 py-3 text-right">Quantity</th>
                        <th scope="col" class="px-5 py-3 text-right">Alert qty.</th>
                        <th scope="col" class="px-5 py-3">Health</th>
                        <th scope="col" class="px-5 py-3 text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($inventory_rows as $row): ?>
                        <?php
                        $quantity = (int) $row->quantity;
                        $alert_quantity = (int) $row->alert_quantity;
                        ?>
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo site_url('products/edit/'.$row->product_id); ?>" class="font-semibold text-slate-900 hover:text-brand-700"><?php echo html_escape($row->product_name); ?></a>
                                    <?php if (!$row->is_active): ?><span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">Disabled</span><?php endif; ?>
                                </div>
                                <p class="mt-1 font-mono text-xs text-slate-400"><?php echo html_escape($row->product_code); ?> · <?php echo html_escape($row->category_name); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-medium text-slate-700"><?php echo html_escape($row->warehouse_name); ?></p>
                                <div class="mt-1 flex items-center gap-2"><p class="font-mono text-xs text-slate-400"><?php echo html_escape($row->warehouse_code); ?></p><?php if (!$row->warehouse_is_active): ?><span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">Disabled</span><?php endif; ?></div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-base font-bold tabular-nums text-slate-900"><?php echo html_escape(number_format($quantity)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right tabular-nums text-slate-500"><?php echo html_escape(number_format($alert_quantity)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <?php if ($quantity === 0): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700"><span class="size-1.5 rounded-full bg-red-500"></span>Out of stock</span>
                                <?php elseif ($quantity <= $alert_quantity): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700"><span class="size-1.5 rounded-full bg-amber-500"></span>Low stock</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>Healthy</span>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <a href="<?php echo site_url('stock/edit/'.$row->warehouse_id.'/'.$row->product_id); ?>" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Adjust</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="px-6 py-16 text-center">
            <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                <svg aria-hidden="true" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V8l7-5 7 5v13" /></svg>
            </span>
            <h2 class="mt-4 font-bold text-slate-900">No inventory positions found</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">Adjust the active filters or add products to the catalog.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($pagination)): ?>
        <div class="border-t border-slate-200 px-4 py-4 sm:px-5">
            <nav aria-label="Inventory pagination">
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
