<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">Overview</nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Operations dashboard</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Monitor the catalog foundation and move quickly into day-to-day ERP tasks.</p>
    </div>
    <a href="<?php echo site_url('products/create'); ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
        </svg>
        Add product
    </a>
</div>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Catalog summary">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Total products</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape($product_summary['total_products']); ?></p>
            </div>
            <span class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8-4 8 4-8 4-8-4Zm0 5 8 4 8-4M4 17l8 4 8-4" /></svg>
            </span>
        </div>
        <a href="<?php echo site_url('products'); ?>" class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-900">View catalog <span aria-hidden="true">→</span></a>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Active products</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape($product_summary['active_products']); ?></p>
            </div>
            <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
            </span>
        </div>
        <p class="mt-4 text-xs text-slate-500">Available for future invoice selection</p>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Categories</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape($category_count); ?></p>
            </div>
            <span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h7" /></svg>
            </span>
        </div>
        <a href="<?php echo site_url('categories'); ?>" class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-900">Manage categories <span aria-hidden="true">→</span></a>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Warehouses</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape($inventory_summary['warehouse_count']); ?></p>
            </div>
            <span class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6" /></svg>
            </span>
        </div>
        <a href="<?php echo site_url('stock'); ?>" class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-900"><?php echo html_escape(number_format($inventory_summary['total_units'])); ?> units on hand <span aria-hidden="true">→</span></a>
    </article>
</section>

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="font-bold text-slate-900">Recent products</h2>
                <p class="mt-1 text-xs text-slate-500">Latest catalog records</p>
            </div>
            <a href="<?php echo site_url('products'); ?>" class="text-sm font-semibold text-brand-700 hover:text-brand-900">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Product</th>
                        <th scope="col" class="px-5 py-3">Category</th>
                        <th scope="col" class="px-5 py-3 text-right">Price</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($recent_products as $product): ?>
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5">
                                <a href="<?php echo site_url('products/edit/'.$product->id); ?>" class="font-semibold text-slate-900 hover:text-brand-700"><?php echo html_escape($product->name); ?></a>
                                <p class="mt-0.5 font-mono text-xs text-slate-400"><?php echo html_escape($product->code); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-slate-600"><?php echo html_escape($product->category_name); ?></td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right font-semibold text-slate-800">$<?php echo html_escape(number_format((float) $product->price, 2)); ?></td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <?php if ($product->is_active): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"><span class="size-1.5 rounded-full bg-slate-400"></span>Disabled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3" /></svg>
            </span>
            <div>
                <h2 class="font-bold text-slate-900">Phase 3</h2>
                <p class="text-xs text-slate-500">Warehouse inventory</p>
            </div>
        </div>
        <div class="mt-5 space-y-3 text-sm">
            <div class="flex items-center gap-3 text-emerald-700"><span class="flex size-5 items-center justify-center rounded-full bg-emerald-100 text-xs">✓</span><span>ERP application shell</span></div>
            <div class="flex items-center gap-3 text-emerald-700"><span class="flex size-5 items-center justify-center rounded-full bg-emerald-100 text-xs">✓</span><span>Tailwind CSS pipeline</span></div>
            <div class="flex items-center gap-3 text-emerald-700"><span class="flex size-5 items-center justify-center rounded-full bg-emerald-100 text-xs">✓</span><span>Product catalog tools</span></div>
            <div class="flex items-center gap-3 text-emerald-700"><span class="flex size-5 items-center justify-center rounded-full bg-emerald-100 text-xs">✓</span><span>Warehouse inventory</span></div>
            <div class="flex items-center gap-3 text-slate-400"><span class="flex size-5 items-center justify-center rounded-full bg-slate-100 text-xs">4</span><span>Customer foundation next</span></div>
        </div>
    </aside>
</div>
