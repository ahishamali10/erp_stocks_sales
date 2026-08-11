<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_nav = isset($active_nav) ? $active_nav : '';
$active_classes = 'bg-brand-600 text-white shadow-sm shadow-brand-900/20';
$inactive_classes = 'text-slate-300 hover:bg-white/10 hover:text-white';
?>
<aside data-sidebar class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-slate-950 text-white shadow-2xl transition-transform duration-200 ease-out lg:translate-x-0" aria-label="ERP navigation">
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-5">
        <a href="<?php echo site_url(); ?>" class="flex min-w-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-400">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-lg shadow-brand-950/40">
                <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8-4 8 4-8 4-8-4Zm0 5 8 4 8-4M4 17l8 4 8-4" />
                </svg>
            </span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-bold tracking-wide">StockFlow ERP</span>
                <span class="block truncate text-[11px] font-medium uppercase tracking-widest text-slate-400">Sales &amp; Inventory</span>
            </span>
        </a>
        <button type="button" data-sidebar-close class="inline-flex size-9 items-center justify-center rounded-lg text-slate-400 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 lg:hidden" aria-label="Close navigation">
            <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-5">
        <div class="space-y-1">
            <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-widest text-slate-500">Overview</p>
            <a href="<?php echo site_url(); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?php echo $active_nav === 'dashboard' ? $active_classes : $inactive_classes; ?>" <?php echo $active_nav === 'dashboard' ? 'aria-current="page"' : ''; ?>>
                <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 13h6V4H4v9Zm0 7h6v-3H4v3Zm10 0h6v-9h-6v9Zm0-13h6V4h-6v3Z" />
                </svg>
                Dashboard
            </a>
        </div>

        <div class="mt-7 space-y-1">
            <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-widest text-slate-500">Catalog</p>
            <a href="<?php echo site_url('products'); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?php echo $active_nav === 'products' ? $active_classes : $inactive_classes; ?>" <?php echo $active_nav === 'products' ? 'aria-current="page"' : ''; ?>>
                <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7 12 3 4 7l8 4 8-4ZM4 12l8 4 8-4M4 17l8 4 8-4" />
                </svg>
                Products
            </a>
            <a href="<?php echo site_url('categories'); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?php echo $active_nav === 'categories' ? $active_classes : $inactive_classes; ?>" <?php echo $active_nav === 'categories' ? 'aria-current="page"' : ''; ?>>
                <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h7" />
                </svg>
                Categories
            </a>
        </div>

        <div class="mt-7 space-y-1">
            <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-widest text-slate-500">Operations</p>
            <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500" aria-disabled="true">
                <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01" />
                </svg>
                Inventory
                <span class="ml-auto rounded-full bg-white/5 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">Soon</span>
            </span>
            <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500" aria-disabled="true">
                <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h10v4H7V3ZM5 7h14v14H5V7Zm4 4h6m-6 4h6" />
                </svg>
                Sales
                <span class="ml-auto rounded-full bg-white/5 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">Soon</span>
            </span>
            <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500" aria-disabled="true">
                <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V9m5 10V5m5 14v-7m5 7V3" />
                </svg>
                Reports
                <span class="ml-auto rounded-full bg-white/5 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">Soon</span>
            </span>
        </div>
    </nav>

    <div class="border-t border-white/10 p-4">
        <div class="rounded-xl bg-white/5 p-3">
            <p class="text-xs font-semibold text-slate-200">Assessment workspace</p>
            <p class="mt-1 text-[11px] leading-5 text-slate-500">PHP 7.4 · CI 3.1.13 · MySQL</p>
        </div>
    </div>
</aside>
