<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
            <a href="<?php echo site_url(); ?>" class="hover:text-brand-700">Operations</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-600">Warehouses</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Warehouses</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Manage physical stock locations without removing their inventory history.</p>
    </div>
    <a href="<?php echo site_url('warehouses/create'); ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
        Add warehouse
    </a>
</div>

<section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Warehouse summary">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Total warehouses</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['total_count'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">All physical locations</p>
    </article>
    <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-emerald-700">Active</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-emerald-950"><?php echo html_escape(number_format($summary['active_count'])); ?></p>
        <p class="mt-3 text-xs text-emerald-700">Available for operations</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-600">Disabled</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900"><?php echo html_escape(number_format($summary['inactive_count'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Preserved for history</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Total units</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950"><?php echo html_escape(number_format($summary['total_units'])); ?></p>
        <p class="mt-3 text-xs text-slate-500">Across every warehouse</p>
    </article>
</section>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-bold text-slate-900">Stock locations</h2>
            <p class="mt-1 text-xs text-slate-500">New locations automatically receive zero stock for every product</p>
        </div>
        <a href="<?php echo site_url('stock'); ?>" class="text-sm font-semibold text-brand-700 hover:text-brand-900">View inventory</a>
    </div>

    <?php if (!empty($warehouses)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Warehouse</th>
                        <th scope="col" class="px-5 py-3 text-right">Positions</th>
                        <th scope="col" class="px-5 py-3 text-right">Units</th>
                        <th scope="col" class="px-5 py-3 text-right">Users</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3 text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($warehouses as $warehouse): ?>
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <a href="<?php echo site_url('warehouses/edit/'.$warehouse->id); ?>" class="font-semibold text-slate-900 hover:text-brand-700"><?php echo html_escape($warehouse->name); ?></a>
                                <p class="mt-1 font-mono text-xs text-slate-400"><?php echo html_escape($warehouse->code); ?></p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right tabular-nums text-slate-600"><?php echo html_escape(number_format((int) $warehouse->inventory_count)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold tabular-nums text-slate-800"><?php echo html_escape(number_format((int) $warehouse->total_units)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right tabular-nums text-slate-600"><?php echo html_escape(number_format((int) $warehouse->user_count)); ?></td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <?php if ($warehouse->is_active): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"><span class="size-1.5 rounded-full bg-slate-400"></span>Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo site_url('warehouses/edit/'.$warehouse->id); ?>" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Edit</a>
                                    <?php if ($warehouse->is_active && (int) $warehouse->user_count > 0): ?>
                                        <button type="button" disabled title="Reassign warehouse users before disabling this location" class="inline-flex min-h-9 cursor-not-allowed items-center justify-center rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-400">Disable</button>
                                    <?php else: ?>
                                        <?php echo form_open('warehouses/toggle-status/'.$warehouse->id, array('class' => 'inline-flex', 'data-confirm' => html_escape(($warehouse->is_active ? 'Disable ' : 'Enable ').$warehouse->name.'?'))); ?>
                                            <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-lg px-3 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 <?php echo $warehouse->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 focus:ring-amber-500' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 focus:ring-emerald-500'; ?>"><?php echo $warehouse->is_active ? 'Disable' : 'Enable'; ?></button>
                                        <?php echo form_close(); ?>
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
            <h2 class="font-bold text-slate-900">No warehouses configured</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">Create a warehouse before recording inventory.</p>
            <a href="<?php echo site_url('warehouses/create'); ?>" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add warehouse</a>
        </div>
    <?php endif; ?>
</section>
