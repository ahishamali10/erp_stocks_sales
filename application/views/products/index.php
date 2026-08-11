<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
            <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Catalog</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-600">Products</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Products</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Manage product identity, pricing, reorder thresholds, and selling availability.</p>
    </div>
    <a href="<?php echo site_url('products/create'); ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
        Add product
    </a>
</div>

<section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 p-4 sm:p-5">
        <form method="get" action="<?php echo site_url('products'); ?>" class="grid gap-3 lg:grid-cols-[minmax(240px,1fr)_minmax(190px,240px)_auto] lg:items-end">
            <div>
                <label for="product-search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search catalog</label>
                <div class="relative">
                    <svg aria-hidden="true" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3.5-3.5" /></svg>
                    <input id="product-search" type="search" name="q" value="<?php echo html_escape($search); ?>" maxlength="200" placeholder="Search by product name or code" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                </div>
            </div>

            <div>
                <label for="category-filter" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Category</label>
                <select id="category-filter" name="category_id" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo html_escape($category->id); ?>" <?php echo (int) $category_id === (int) $category->id ? 'selected' : ''; ?>><?php echo html_escape($category->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-600 focus:ring-offset-2 lg:flex-none">Apply filters</button>
                <?php if ($search !== '' || $category_id > 0): ?>
                    <a href="<?php echo site_url('products'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <p>Showing <span class="font-semibold text-slate-700"><?php echo html_escape($result_from); ?></span>–<span class="font-semibold text-slate-700"><?php echo html_escape($result_to); ?></span> of <span class="font-semibold text-slate-700"><?php echo html_escape($total_rows); ?></span> products</p>
        <?php if ($search !== ''): ?>
            <p>Search: <span class="font-semibold text-slate-700">“<?php echo html_escape($search); ?>”</span></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($products)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Product</th>
                        <th scope="col" class="px-5 py-3">Category</th>
                        <th scope="col" class="px-5 py-3 text-right">Price</th>
                        <th scope="col" class="px-5 py-3 text-right">Alert qty.</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3 text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($products as $product): ?>
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <a href="<?php echo site_url('products/edit/'.$product->id); ?>" class="font-semibold text-slate-900 hover:text-brand-700"><?php echo html_escape($product->name); ?></a>
                                <p class="mt-1 font-mono text-xs text-slate-400"><?php echo html_escape($product->code); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600"><?php echo html_escape($product->category_name); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold tabular-nums text-slate-800">$<?php echo html_escape(number_format((float) $product->price, 2)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right tabular-nums text-slate-600"><?php echo html_escape(number_format((int) $product->alert_quantity)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <?php if ($product->is_active): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"><span class="size-1.5 rounded-full bg-slate-400"></span>Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo site_url('products/edit/'.$product->id); ?>" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Edit</a>
                                    <?php echo form_open('products/toggle-status/'.$product->id, array('class' => 'inline-flex', 'data-confirm' => $product->is_active ? 'Disable this product?' : 'Enable this product?')); ?>
                                        <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-lg px-3 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 <?php echo $product->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 focus:ring-amber-500' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 focus:ring-emerald-500'; ?>">
                                            <?php echo $product->is_active ? 'Disable' : 'Enable'; ?>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="px-6 py-16 text-center">
            <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                <svg aria-hidden="true" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3.5-3.5" /></svg>
            </span>
            <h2 class="mt-4 font-bold text-slate-900">No products found</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">Adjust the active filters or create a new product record.</p>
            <a href="<?php echo site_url('products/create'); ?>" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add product</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($pagination)): ?>
        <div class="border-t border-slate-200 px-4 py-4 sm:px-5">
            <nav aria-label="Product pagination">
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
