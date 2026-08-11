(function () {
    'use strict';

    var root = document.querySelector('[data-sales-invoice]');

    if (!root) {
        return;
    }

    var form = root.querySelector('[data-invoice-form]');
    var warehouseSelect = root.querySelector('[data-warehouse-select]');
    var searchInput = root.querySelector('[data-product-search]');
    var searchButton = root.querySelector('[data-search-button]');
    var searchStatus = root.querySelector('[data-search-status]');
    var resultsContainer = root.querySelector('[data-product-results]');
    var linesContainer = root.querySelector('[data-invoice-lines]');
    var linesEmpty = root.querySelector('[data-lines-empty]');
    var linesTable = root.querySelector('[data-lines-table]');
    var lineCount = root.querySelector('[data-line-count]');
    var discountInput = root.querySelector('[data-discount]');
    var subtotalOutput = root.querySelector('[data-subtotal]');
    var discountOutput = root.querySelector('[data-discount-amount]');
    var totalOutput = root.querySelector('[data-total]');
    var saveButton = root.querySelector('[data-save-invoice]');
    var saveLabel = root.querySelector('[data-save-label]');
    var feedback = root.querySelector('[data-invoice-feedback]');
    var successPanel = root.querySelector('[data-invoice-success]');
    var successNumber = root.querySelector('[data-success-number]');
    var successTotal = root.querySelector('[data-success-total]');
    var lines = new Map();
    var searchTimer = null;
    var searchRequest = null;

    function money(cents) {
        return '$' + (cents / 100).toFixed(2);
    }

    function priceToCents(price) {
        var parts = String(price).split('.');
        var whole = parseInt(parts[0], 10) || 0;
        var fraction = parts.length > 1 ? (parts[1] + '00').slice(0, 2) : '00';

        return (whole * 100) + (parseInt(fraction, 10) || 0);
    }

    function discountBasis() {
        var value = String(discountInput.value || '').trim();
        var match = value.match(/^(\d{1,3})(?:\.(\d{1,2}))?$/);

        if (!match) {
            return 0;
        }

        var basis = (parseInt(match[1], 10) * 100) + parseInt(((match[2] || '') + '00').slice(0, 2), 10);

        return Math.min(10000, Math.max(0, basis));
    }

    function setFeedback(message, type) {
        feedback.textContent = message;
        feedback.className = 'mb-6 rounded-xl border px-4 py-3 text-sm ' + (type === 'error'
            ? 'border-red-200 bg-red-50 text-red-800'
            : 'border-brand-200 bg-brand-50 text-brand-900');
    }

    function clearFeedback() {
        feedback.textContent = '';
        feedback.className = 'mb-6 hidden rounded-xl border px-4 py-3 text-sm';
    }

    function updateCsrf(payload) {
        if (!payload || !payload.csrf || !payload.csrf.name || !payload.csrf.hash) {
            return;
        }

        var token = form.querySelector('input[name="' + payload.csrf.name + '"]');

        if (token) {
            token.value = payload.csrf.hash;
        }
    }

    function updateTotals() {
        var subtotalCents = 0;

        lines.forEach(function (line) {
            var lineTotal = line.priceCents * Math.max(0, line.quantity || 0);
            var output = linesContainer.querySelector('[data-line-total="' + line.id + '"]');

            subtotalCents += lineTotal;

            if (output) {
                output.textContent = money(lineTotal);
            }
        });

        var discountCents = Math.round(subtotalCents * discountBasis() / 10000);
        var totalCents = subtotalCents - discountCents;

        subtotalOutput.textContent = money(subtotalCents);
        discountOutput.textContent = '-' + money(discountCents);
        totalOutput.textContent = money(totalCents);
        saveButton.disabled = lines.size === 0;
    }

    function renderLines() {
        linesContainer.textContent = '';
        linesEmpty.classList.toggle('hidden', lines.size > 0);
        linesTable.classList.toggle('hidden', lines.size === 0);
        lineCount.textContent = lines.size + (lines.size === 1 ? ' line' : ' lines');

        lines.forEach(function (line) {
            var row = document.createElement('tr');
            var productCell = document.createElement('td');
            var name = document.createElement('p');
            var code = document.createElement('p');
            var productInput = document.createElement('input');
            var priceCell = document.createElement('td');
            var quantityCell = document.createElement('td');
            var quantityInput = document.createElement('input');
            var available = document.createElement('p');
            var totalCell = document.createElement('td');
            var removeCell = document.createElement('td');
            var removeButton = document.createElement('button');

            row.className = 'hover:bg-slate-50/80';
            productCell.className = 'px-5 py-4';
            name.className = 'font-semibold text-slate-900';
            name.textContent = line.name;
            code.className = 'mt-1 font-mono text-xs text-slate-400';
            code.textContent = line.code;
            productInput.type = 'hidden';
            productInput.name = 'product_id[]';
            productInput.value = line.id;
            productCell.appendChild(name);
            productCell.appendChild(code);
            productCell.appendChild(productInput);

            priceCell.className = 'whitespace-nowrap px-5 py-4 text-right font-semibold tabular-nums text-slate-700';
            priceCell.textContent = money(line.priceCents);

            quantityCell.className = 'px-5 py-4 text-center';
            quantityInput.type = 'number';
            quantityInput.name = 'quantity[]';
            quantityInput.value = line.quantity;
            quantityInput.min = '1';
            quantityInput.max = String(line.available);
            quantityInput.step = '1';
            quantityInput.required = true;
            quantityInput.className = 'min-h-10 w-24 rounded-lg border border-slate-300 px-2 py-2 text-center text-sm tabular-nums outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
            quantityInput.addEventListener('input', function () {
                var parsed = parseInt(quantityInput.value, 10);
                line.quantity = Number.isFinite(parsed) ? parsed : 0;
                updateTotals();
            });
            quantityInput.addEventListener('change', function () {
                line.quantity = Math.min(line.available, Math.max(1, parseInt(quantityInput.value, 10) || 1));
                quantityInput.value = line.quantity;
                updateTotals();
            });
            available.className = 'mt-1 text-xs text-slate-400';
            available.textContent = line.available + ' available';
            quantityCell.appendChild(quantityInput);
            quantityCell.appendChild(available);

            totalCell.className = 'whitespace-nowrap px-5 py-4 text-right font-bold tabular-nums text-slate-900';
            totalCell.setAttribute('data-line-total', line.id);

            removeCell.className = 'px-5 py-4 text-right';
            removeButton.type = 'button';
            removeButton.className = 'inline-flex min-h-9 items-center justify-center rounded-lg bg-red-50 px-3 text-xs font-semibold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2';
            removeButton.textContent = 'Remove';
            removeButton.addEventListener('click', function () {
                lines.delete(line.id);
                renderLines();
            });
            removeCell.appendChild(removeButton);

            row.appendChild(productCell);
            row.appendChild(priceCell);
            row.appendChild(quantityCell);
            row.appendChild(totalCell);
            row.appendChild(removeCell);
            linesContainer.appendChild(row);
        });

        updateTotals();
    }

    function addProduct(product) {
        var key = String(product.id);
        var existing = lines.get(key);

        if (existing) {
            existing.available = product.available_quantity;
            existing.priceCents = priceToCents(product.price);

            if (existing.quantity >= existing.available) {
                setFeedback('The available quantity for ' + existing.code + ' is already on the invoice.', 'error');
                return;
            }

            existing.quantity += 1;
        } else {
            lines.set(key, {
                id: key,
                code: product.code,
                name: product.name,
                priceCents: priceToCents(product.price),
                available: product.available_quantity,
                quantity: 1
            });
        }

        clearFeedback();
        renderLines();
    }

    function renderResults(products) {
        resultsContainer.textContent = '';

        if (!products.length) {
            searchStatus.textContent = 'No active products match this search.';
            return;
        }

        searchStatus.textContent = products.length + (products.length === 1 ? ' product found' : ' products found');

        products.forEach(function (product) {
            var card = document.createElement('div');
            var details = document.createElement('div');
            var title = document.createElement('p');
            var meta = document.createElement('p');
            var action = document.createElement('button');

            card.className = 'flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between';
            details.className = 'min-w-0';
            title.className = 'truncate text-sm font-semibold text-slate-900';
            title.textContent = product.name;
            meta.className = 'mt-1 text-xs text-slate-500';
            meta.textContent = product.code + ' · ' + money(priceToCents(product.price)) + ' · ' + product.available_quantity + ' available';
            details.appendChild(title);
            details.appendChild(meta);

            action.type = 'button';
            action.className = 'inline-flex min-h-9 shrink-0 items-center justify-center rounded-lg px-3 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 ' + (product.available_quantity > 0
                ? 'bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-500'
                : 'cursor-not-allowed bg-slate-200 text-slate-400');
            action.textContent = product.available_quantity > 0 ? 'Add' : 'Out of stock';
            action.disabled = product.available_quantity < 1;
            action.addEventListener('click', function () {
                addProduct(product);
            });

            card.appendChild(details);
            card.appendChild(action);
            resultsContainer.appendChild(card);
        });
    }

    function readJson(response) {
        return response.json().catch(function () {
            throw new Error(response.status === 403
                ? 'Your form session expired. Refresh the page and try again.'
                : 'The server returned an unexpected response. Please try again.');
        }).then(function (payload) {
            updateCsrf(payload);

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'The request could not be completed.');
            }

            return payload;
        });
    }

    function runSearch() {
        var warehouseId = warehouseSelect.value;

        if (!warehouseId) {
            setFeedback('Select a warehouse before searching products.', 'error');
            return;
        }

        if (searchRequest && typeof searchRequest.abort === 'function') {
            searchRequest.abort();
        }

        searchRequest = typeof AbortController !== 'undefined' ? new AbortController() : null;
        searchButton.disabled = true;
        searchButton.textContent = 'Searching...';
        searchStatus.textContent = 'Loading current warehouse stock...';
        resultsContainer.textContent = '';

        var url = root.getAttribute('data-search-url')
            + '?warehouse_id=' + encodeURIComponent(warehouseId)
            + '&q=' + encodeURIComponent(searchInput.value.trim());
        var options = {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        };

        if (searchRequest) {
            options.signal = searchRequest.signal;
        }

        fetch(url, options)
            .then(readJson)
            .then(function (payload) {
                renderResults(payload.data || []);
            })
            .catch(function (error) {
                if (error.name !== 'AbortError') {
                    searchStatus.textContent = 'Product search failed.';
                    setFeedback(error.message, 'error');
                }
            })
            .finally(function () {
                searchButton.disabled = !warehouseSelect.value;
                searchButton.textContent = 'Search';
            });
    }

    warehouseSelect.addEventListener('change', function () {
        if (searchRequest && typeof searchRequest.abort === 'function') {
            searchRequest.abort();
        }

        searchInput.disabled = !warehouseSelect.value;
        searchButton.disabled = !warehouseSelect.value;
        searchInput.value = '';
        resultsContainer.textContent = '';
        successPanel.classList.add('hidden');

        if (lines.size > 0) {
            lines.clear();
            renderLines();
            setFeedback('Invoice lines were cleared because the warehouse changed.', 'info');
        } else {
            clearFeedback();
        }

        searchStatus.textContent = warehouseSelect.value
            ? 'Search by product name or code.'
            : 'Select a warehouse to search products.';
    });

    searchButton.addEventListener('click', runSearch);
    searchInput.addEventListener('input', function () {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(runSearch, 300);
    });
    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            window.clearTimeout(searchTimer);
            runSearch();
        }
    });
    discountInput.addEventListener('input', updateTotals);

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        clearFeedback();
        successPanel.classList.add('hidden');

        if (!form.reportValidity()) {
            return;
        }

        if (lines.size < 1) {
            setFeedback('Add at least one product line before saving.', 'error');
            return;
        }

        saveButton.disabled = true;
        saveLabel.textContent = 'Saving invoice...';

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(readJson)
            .then(function (payload) {
                successNumber.textContent = payload.data.invoice_number;
                successTotal.textContent = money(priceToCents(payload.data.total));
                successPanel.classList.remove('hidden');
                lines.clear();
                discountInput.value = '0';
                resultsContainer.textContent = '';
                searchStatus.textContent = 'Invoice saved. Search again to load refreshed stock.';
                renderLines();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(function (error) {
                setFeedback(error.message, 'error');
            })
            .finally(function () {
                saveLabel.textContent = 'Save invoice';
                saveButton.disabled = lines.size === 0;
            });
    });

    renderLines();
}());
