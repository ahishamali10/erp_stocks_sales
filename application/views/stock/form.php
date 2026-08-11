<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$quantity_value = set_value('quantity', $inventory->quantity, FALSE);
$quantity_error = trim(strip_tags(form_error('quantity')));
$back_url = site_url('stock').'?warehouse_id='.rawurlencode($inventory->warehouse_id);
?>
<div class="mb-6">
    <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
        <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Operations</a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo html_escape($back_url); ?>" class="hover:text-brand-700">Inventory</a>
        <span aria-hidden="true">/</span>
        <span class="text-slate-600">Adjust</span>
    </nav>
    <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Adjust inventory</h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Set the trusted on-hand quantity for this exact warehouse position.</p>
</div>

<?php if ($save_error !== ''): ?>
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><?php echo html_escape($save_error); ?></div>
<?php endif; ?>

<div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="font-bold text-slate-900"><?php echo html_escape($inventory->product_name); ?></h2>
            <p class="mt-1 font-mono text-xs text-slate-500"><?php echo html_escape($inventory->product_code); ?> · <?php echo html_escape($inventory->category_name); ?></p>
        </div>

        <?php echo form_open('stock/update/'.$inventory->warehouse_id.'/'.$inventory->product_id, array('class' => 'p-5 sm:p-6')); ?>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Warehouse</p>
                    <p class="mt-2 font-semibold text-slate-900"><?php echo html_escape($inventory->warehouse_name); ?></p>
                    <p class="mt-1 font-mono text-xs text-slate-500"><?php echo html_escape($inventory->warehouse_code); ?></p>
                    <?php if (!$inventory->warehouse_is_active): ?><p class="mt-2 text-xs font-semibold text-amber-700">This warehouse is disabled.</p><?php endif; ?>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alert quantity</p>
                    <p class="mt-2 text-xl font-bold tabular-nums text-slate-900"><?php echo html_escape(number_format((int) $inventory->alert_quantity)); ?></p>
                    <p class="mt-1 text-xs text-slate-500">Low-stock threshold</p>
                </div>
                <div class="sm:col-span-2">
                    <label for="stock-quantity" class="mb-1.5 block text-sm font-semibold text-slate-700">On-hand quantity <span class="text-red-500" aria-hidden="true">*</span></label>
                    <input id="stock-quantity" type="number" name="quantity" value="<?php echo html_escape($quantity_value); ?>" min="0" max="2147483647" step="1" required autofocus inputmode="numeric" class="min-h-12 w-full rounded-xl border bg-white px-3 py-2.5 text-lg font-bold tabular-nums text-slate-900 outline-none transition focus:ring-4 <?php echo $quantity_error ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                    <?php if ($quantity_error): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($quantity_error); ?></p><?php endif; ?>
                    <p class="mt-1.5 text-xs text-slate-500">Enter the complete physical quantity currently available at this warehouse.</p>
                </div>
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="<?php echo html_escape($back_url); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Cancel</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9 16l10-10" /></svg>
                    Save quantity
                </button>
            </div>
        <?php echo form_close(); ?>
    </section>

    <aside class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Current position</p>
            <p class="mt-3 text-3xl font-bold tabular-nums text-slate-950"><?php echo html_escape(number_format((int) $inventory->quantity)); ?></p>
            <p class="mt-1 text-xs text-slate-500">Units currently recorded</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-900">
            <h2 class="font-bold">Adjustment note</h2>
            <p class="mt-2">This replaces the recorded quantity. Confirm the physical count before saving.</p>
        </div>
    </aside>
</div>
