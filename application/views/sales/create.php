<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div data-sales-invoice data-search-url="<?php echo html_escape(site_url('sales/search-products')); ?>">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
                <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Operations</a>
                <span aria-hidden="true">/</span>
                <span class="text-slate-600">New sale</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">New sales invoice</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Select a customer and warehouse, then add products from live stock.</p>
        </div>
        <span class="inline-flex items-center gap-2 self-start rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
            <span class="size-2 rounded-full bg-emerald-500"></span>
            Server-validated totals
        </span>
    </div>

    <div data-invoice-feedback class="mb-6 hidden rounded-xl border px-4 py-3 text-sm" role="alert" aria-live="polite"></div>

    <section data-invoice-success class="mb-6 hidden rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm" aria-live="polite">
        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Invoice saved</p>
        <div class="mt-2 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p data-success-number class="text-xl font-bold text-emerald-950"></p>
                <p class="mt-1 text-sm text-emerald-700">Stock was deducted from the selected warehouse.</p>
            </div>
            <p class="text-sm font-semibold text-emerald-900">Total <span data-success-total class="text-lg"></span></p>
        </div>
    </section>

    <?php echo form_open('sales/store', array('data-invoice-form' => '', 'class' => 'space-y-6')); ?>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="grid gap-5 lg:grid-cols-2">
                <div>
                    <label for="sale-customer" class="mb-1.5 block text-sm font-semibold text-slate-700">Customer <span class="text-red-500" aria-hidden="true">*</span></label>
                    <select id="sale-customer" name="customer_id" required class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        <option value="">Select customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo html_escape($customer->id); ?>"><?php echo html_escape($customer->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($customers)): ?><p class="mt-1.5 text-xs font-medium text-red-600">Add a customer before creating an invoice.</p><?php endif; ?>
                </div>
                <div>
                    <label for="sale-warehouse" class="mb-1.5 block text-sm font-semibold text-slate-700">Warehouse <span class="text-red-500" aria-hidden="true">*</span></label>
                    <select id="sale-warehouse" name="warehouse_id" required data-warehouse-select class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        <option value="">Select warehouse</option>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <option value="<?php echo html_escape($warehouse->id); ?>"><?php echo html_escape($warehouse->name); ?> (<?php echo html_escape($warehouse->code); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($warehouses)): ?><p class="mt-1.5 text-xs font-medium text-red-600">No active warehouse is available for sales.</p><?php endif; ?>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                        <h2 class="font-bold text-slate-900">Add products</h2>
                        <p class="mt-1 text-xs text-slate-500">Results use the current selected warehouse stock.</p>
                    </div>
                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="relative min-w-0 flex-1">
                                <svg aria-hidden="true" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3.5-3.5" /></svg>
                                <input type="search" data-product-search maxlength="100" autocomplete="off" placeholder="Search product name or code" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100" disabled>
                            </div>
                            <button type="button" data-search-button class="inline-flex min-h-11 items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" disabled>Search</button>
                        </div>
                        <div data-search-status class="mt-3 text-xs text-slate-500">Select a warehouse to search products.</div>
                        <div data-product-results class="mt-4 grid gap-2"></div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
                        <div>
                            <h2 class="font-bold text-slate-900">Invoice lines</h2>
                            <p class="mt-1 text-xs text-slate-500">Duplicate products are combined into one line.</p>
                        </div>
                        <span data-line-count class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">0 lines</span>
                    </div>
                    <div data-lines-empty class="px-6 py-14 text-center">
                        <p class="font-semibold text-slate-800">No products added</p>
                        <p class="mt-1 text-sm text-slate-500">Search the selected warehouse and add an available item.</p>
                    </div>
                    <div data-lines-table class="hidden overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th scope="col" class="px-5 py-3">Product</th>
                                    <th scope="col" class="px-5 py-3 text-right">Price</th>
                                    <th scope="col" class="px-5 py-3 text-center">Quantity</th>
                                    <th scope="col" class="px-5 py-3 text-right">Line total</th>
                                    <th scope="col" class="px-5 py-3"><span class="sr-only">Remove</span></th>
                                </tr>
                            </thead>
                            <tbody data-invoice-lines class="divide-y divide-slate-100 bg-white"></tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">Invoice summary</h2>
                    <div class="mt-5">
                        <label for="sale-discount" class="mb-1.5 block text-sm font-semibold text-slate-700">Discount percentage</label>
                        <div class="relative">
                            <input id="sale-discount" type="number" name="discount_percentage" value="0" min="0" max="100" step="0.01" inputmode="decimal" data-discount class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-9 text-right text-sm tabular-nums text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">%</span>
                        </div>
                    </div>
                    <dl class="mt-5 space-y-3 border-t border-slate-200 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-4 text-slate-600"><dt>Subtotal</dt><dd data-subtotal class="font-semibold tabular-nums text-slate-900">$0.00</dd></div>
                        <div class="flex items-center justify-between gap-4 text-slate-600"><dt>Discount</dt><dd data-discount-amount class="font-semibold tabular-nums text-slate-900">-$0.00</dd></div>
                        <div class="flex items-center justify-between gap-4 border-t border-slate-200 pt-4"><dt class="font-bold text-slate-900">Total</dt><dd data-total class="text-xl font-bold tabular-nums text-slate-950">$0.00</dd></div>
                    </dl>
                    <button type="submit" data-save-invoice class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" disabled>
                        <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9 16l10-10" /></svg>
                        <span data-save-label>Save invoice</span>
                    </button>
                    <p class="mt-3 text-center text-xs leading-5 text-slate-500">Prices, totals, active status, and stock are checked again by the server.</p>
                </section>

                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                    <h2 class="font-bold text-amber-950">Stock integrity</h2>
                    <p class="mt-2 text-xs leading-5 text-amber-800">Saving locks inventory rows in product order. If any quantity is unavailable, the complete invoice is rejected and no stock is deducted.</p>
                </section>
            </aside>
        </div>
    <?php echo form_close(); ?>
</div>
<script src="<?php echo base_url('assets/js/sales-invoice.js'); ?>" defer></script>
