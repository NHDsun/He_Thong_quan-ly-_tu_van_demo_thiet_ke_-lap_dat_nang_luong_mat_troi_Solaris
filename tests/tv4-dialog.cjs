const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../resources/js/installation.js'), 'utf8');

for (const readyState of ['loading', 'complete']) {
    const handlers = {};
    const dialogHandlers = {};
    let openCount = 0;
    let focused = false;
    let click;
    let close;
    let searchInput;
    let categoryChange;
    const search = { value: '', addEventListener: (_, fn) => { searchInput = fn; } };
    const category = { value: '', addEventListener: (_, fn) => { categoryChange = fn; } };
    const count = { textContent: '' };
    const products = {
        value: '',
        options: [
            { value: '' },
            { value: '1', dataset: { name: 'Tấm pin năng lượng', category: '1' } },
            { value: '2', dataset: { name: 'Đầu nối cáp', category: '2' } },
            ...Array.from({ length: 23 }, (_, i) => ({ value: String(i + 3), dataset: { name: 'Cáp ' + i, category: '2' } })),
        ],
        replaceChildren: function (...options) { this.options = options; },
    };
    const opener = { addEventListener: (_, fn) => { click = fn; }, focus: () => { focused = true; } };
    const dialog = {
        dataset: { autoOpen: '0' },
        showModal: () => { openCount++; },
        close: () => { dialogHandlers.close(); },
        addEventListener: (name, fn) => { dialogHandlers[name] = fn; },
        querySelector: (selector) => ({
            '[data-material-search]': search,
            '[data-material-category]': category,
            '[data-material-products]': products,
            '[data-material-count]': count,
            '[data-material-close]': { addEventListener: (_, fn) => { close = fn; } },
        }[selector] ?? null),
    };
    const document = {
        readyState,
        getElementById: () => null,
        createElement: () => ({}),
        querySelector: (selector) => selector === '#tv4-material-dialog' ? dialog : selector.startsWith('[data-material-open=') ? opener : null,
        addEventListener: (event, fn) => { (handlers[event] ??= []).push(fn); },
    };
    const context = { document, window: { location: { hash: '' }, addEventListener: () => {} } };
    vm.runInNewContext(source, context);
    for (const handler of handlers.DOMContentLoaded ?? []) { handler(); }
    click();
    assert.equal(openCount, 1);
    close();
    assert.equal(focused, true);
    context.initInstallationMaterialDialog();
    click();
    assert.equal(openCount, 2);
    console.log(`PASS dialog open/close and single initialization: ${readyState}`);
    assert.equal(products.options.length, 26);
    search.value = 'dau noi';
    searchInput();
    assert.equal(products.options.length, 2);
    assert.equal(products.options[1].value, '2');
    products.value = '2';
    category.value = '1';
    categoryChange();
    assert.equal(products.options.length, 1);
    assert.equal(products.value, '');
    search.value = '';
    category.value = '';
    searchInput();
    assert.equal(products.options.length, 26);
    assert.equal(count.textContent, '25 vật tư');
    console.log(`PASS search without accents, category filter, and restore all 25 materials: ${readyState}`);
    const accordion = { open: false, matches: (selector) => selector === 'details.tv4-stage' };
    document.getElementById = (id) => id === 'stage-7' ? accordion : null;
    context.window.location.hash = '#stage-7';
    context.openInstallationStageFromHash();
    assert.equal(accordion.open, true);
    console.log(`PASS timeline link opens completed stage accordion: ${readyState}`);
}
