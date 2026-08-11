<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$category_value = set_value('category_id', $product ? $product->category_id : '');
$code_value = set_value('code', $product ? $product->code : '');
$name_value = set_value('name', $product ? $product->name : '');
$price_value = set_value('price', $product ? $product->price : '0.00');
$alert_quantity_value = set_value('alert_quantity', $product ? $product->alert_quantity : '0');
$field_error = function ($field) {
    return trim(strip_tags(form_error($field)));
};
?>
<div class="mb-6">
    <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
        <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Catalog</a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo site_url('products'); ?>" class="hover:text-brand-700">Products</a>
        <span aria-hidden="true">/</span>
        <span class="text-slate-600"><?php echo $product ? 'Edit' : 'Add'; ?></span>
    </nav>
    <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl"><?php echo html_escape($page_title); ?></h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"><?php echo html_escape($page_description); ?></p>
</div>

<?php if ($save_error !== ''): ?>
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><?php echo html_escape($save_error); ?></div>
<?php endif; ?>

<div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(260px,1fr)]">
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="font-bold text-slate-900">Product information</h2>
            <p class="mt-1 text-xs text-slate-500">Fields marked required must be provided.</p>
        </div>

        <?php echo form_open($form_action, array('class' => 'p-5 sm:p-6')); ?>
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="category-id" class="mb-1.5 block text-sm font-semibold text-slate-700">Category <span class="text-red-500" aria-hidden="true">*</span></label>
                    <select id="category-id" name="category_id" required class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:ring-4 <?php echo $field_error('category_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo html_escape($category->id); ?>" <?php echo (string) $category_value === (string) $category->id ? 'selected' : ''; ?>><?php echo html_escape($category->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($field_error('category_id')): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($field_error('category_id')); ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="product-code" class="mb-1.5 block text-sm font-semibold text-slate-700">Product code <span class="text-red-500" aria-hidden="true">*</span></label>
                    <input id="product-code" type="text" name="code" value="<?php echo html_escape($code_value); ?>" maxlength="100" required autocomplete="off" placeholder="e.g. P007" class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 font-mono text-sm uppercase text-slate-900 outline-none transition placeholder:font-sans placeholder:normal-case placeholder:text-slate-400 focus:ring-4 <?php echo $field_error('code') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                    <?php if ($field_error('code')): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($field_error('code')); ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="product-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Product name <span class="text-red-500" aria-hidden="true">*</span></label>
                    <input id="product-name" type="text" name="name" value="<?php echo html_escape($name_value); ?>" maxlength="200" required autocomplete="off" placeholder="Enter a descriptive name" class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 <?php echo $field_error('name') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                    <?php if ($field_error('name')): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($field_error('name')); ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="product-price" class="mb-1.5 block text-sm font-semibold text-slate-700">Unit price <span class="text-red-500" aria-hidden="true">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">$</span>
                        <input id="product-price" type="number" name="price" value="<?php echo html_escape($price_value); ?>" min="0" max="9999999999.99" step="0.01" required inputmode="decimal" class="min-h-11 w-full rounded-xl border bg-white py-2.5 pl-8 pr-3 text-sm tabular-nums text-slate-900 outline-none transition focus:ring-4 <?php echo $field_error('price') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                    </div>
                    <?php if ($field_error('price')): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($field_error('price')); ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="alert-quantity" class="mb-1.5 block text-sm font-semibold text-slate-700">Alert quantity <span class="text-red-500" aria-hidden="true">*</span></label>
                    <input id="alert-quantity" type="number" name="alert_quantity" value="<?php echo html_escape($alert_quantity_value); ?>" min="0" max="2147483647" step="1" required inputmode="numeric" class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm tabular-nums text-slate-900 outline-none transition focus:ring-4 <?php echo $field_error('alert_quantity') ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                    <p class="mt-1.5 text-xs text-slate-500">Low stock is reported when quantity is at or below this value.</p>
                    <?php if ($field_error('alert_quantity')): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($field_error('alert_quantity')); ?></p><?php endif; ?>
                </div>
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="<?php echo site_url('products'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Cancel</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9 16l10-10" /></svg>
                    <?php echo html_escape($submit_label); ?>
                </button>
            </div>
        <?php echo form_close(); ?>
    </section>

    <aside class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold text-slate-900">Catalog guidance</h2>
            <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>Codes must be unique and are saved in uppercase.</span></li>
                <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>Prices are stored as fixed two-decimal values.</span></li>
                <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>Disable products from the list instead of deleting them.</span></li>
            </ul>
        </div>

        <?php if ($product): ?>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Record status</p>
                <div class="mt-3 flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold text-slate-700"><?php echo $product->is_active ? 'Active' : 'Disabled'; ?></span>
                    <span class="size-2.5 rounded-full <?php echo $product->is_active ? 'bg-emerald-500' : 'bg-slate-400'; ?>"></span>
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-500">Status changes are handled from the product list using a confirmed POST action.</p>
            </div>
        <?php endif; ?>
    </aside>
</div>
