<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$name_value = set_value('name', $category ? $category->name : '', FALSE);
$name_error = trim(strip_tags(form_error('name')));
?>
<div class="mb-6">
    <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
        <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Catalog</a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo site_url('categories'); ?>" class="hover:text-brand-700">Categories</a>
        <span aria-hidden="true">/</span>
        <span class="text-slate-600"><?php echo $category ? 'Edit' : 'Add'; ?></span>
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
            <h2 class="font-bold text-slate-900">Category information</h2>
            <p class="mt-1 text-xs text-slate-500">Use a concise name that operators can recognize quickly.</p>
        </div>

        <?php echo form_open($form_action, array('class' => 'p-5 sm:p-6')); ?>
            <div>
                <label for="category-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Category name <span class="text-red-500" aria-hidden="true">*</span></label>
                <input id="category-name" type="text" name="name" value="<?php echo html_escape($name_value); ?>" maxlength="150" required autofocus autocomplete="off" placeholder="e.g. Electronics" class="min-h-11 w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 <?php echo $name_error ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-100'; ?>">
                <?php if ($name_error): ?><p class="mt-1.5 text-xs font-medium text-red-600"><?php echo html_escape($name_error); ?></p><?php endif; ?>
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="<?php echo site_url('categories'); ?>" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Cancel</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9 16l10-10" /></svg>
                    <?php echo html_escape($submit_label); ?>
                </button>
            </div>
        <?php echo form_close(); ?>
    </section>

    <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-bold text-slate-900">Category rules</h2>
        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
            <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>Names must be unique and may contain up to 150 characters.</span></li>
            <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>Renaming a category updates how it appears throughout the catalog.</span></li>
            <li class="flex gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-500"></span><span>Categories assigned to products cannot be deleted.</span></li>
        </ul>
    </aside>
</div>
