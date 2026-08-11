<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
            <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Catalog</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-600">Categories</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Categories</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Maintain the groups used to organize and filter products.</p>
    </div>
    <a href="<?php echo site_url('categories/create'); ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
        Add category
    </a>
</div>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-bold text-slate-900">Catalog groups</h2>
            <p class="mt-1 text-xs text-slate-500"><?php echo html_escape(count($categories)); ?> categories configured</p>
        </div>
        <p class="text-xs text-slate-500">Categories in use cannot be deleted</p>
    </div>

    <?php if (!empty($categories)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Category</th>
                        <th scope="col" class="px-5 py-3 text-right">Products</th>
                        <th scope="col" class="px-5 py-3">Updated</th>
                        <th scope="col" class="px-5 py-3 text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($categories as $category): ?>
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <a href="<?php echo site_url('categories/edit/'.$category->id); ?>" class="font-semibold text-slate-900 hover:text-brand-700"><?php echo html_escape($category->name); ?></a>
                                <p class="mt-1 text-xs text-slate-400">ID <?php echo html_escape($category->id); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <a href="<?php echo site_url('products').'?category_id='.rawurlencode($category->id); ?>" class="inline-flex min-w-9 items-center justify-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-100">
                                    <?php echo html_escape((int) $category->product_count); ?>
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-500"><?php echo html_escape(date('M j, Y', strtotime($category->updated_at))); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo site_url('categories/edit/'.$category->id); ?>" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Edit</a>
                                    <?php if ((int) $category->product_count === 0): ?>
                                        <?php echo form_open('categories/delete/'.$category->id, array('class' => 'inline-flex', 'data-confirm' => html_escape('Delete category '.$category->name.'? This cannot be undone.'))); ?>
                                            <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-red-50 px-3 text-xs font-semibold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Delete</button>
                                        <?php echo form_close(); ?>
                                    <?php else: ?>
                                        <button type="button" disabled title="Reassign its products before deleting this category" class="inline-flex min-h-9 cursor-not-allowed items-center justify-center rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-400">Delete</button>
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
            <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                <svg aria-hidden="true" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h10M4 18h7" /></svg>
            </span>
            <h2 class="mt-4 font-bold text-slate-900">No categories configured</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">Create a category before adding products.</p>
            <a href="<?php echo site_url('categories/create'); ?>" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add category</a>
        </div>
    <?php endif; ?>
</section>
