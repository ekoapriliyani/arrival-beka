<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} · Kelola Supplier</title>
    <!-- CSS UI Sederhana -->
    <style>
        body { font-family: Arial, sans-serif; padding: 15px; margin: 0; background-color: #f4f6f9; }
        .container { max-width: 720px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-top: 0; }
        h3 { margin-bottom: 8px; font-size: 15px; }
        button { padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px; }
        button:disabled { background-color: #adb5bd; color: #f4f6f9; cursor: not-allowed; }
        .btn-save { background-color: #007bff; color: white; width: 100%; }
        .btn-cancel { background-color: #6c757d; color: white; }
        .btn-edit { background-color: #fd7e14; color: white; padding: 6px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }
        .btn-delete { background-color: #dc3545; color: white; padding: 6px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 10px; }
        input, select { display: block; width: 100%; box-sizing: border-box; margin-top: 4px; padding: 9px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; font-family: inherit; background: white; }
        .form-errors { background: #fdecea; color: #b3261e; border: 1px solid #f5c6cb; border-radius: 5px; padding: 10px; margin-bottom: 10px; font-size: 13px; }
        .form-errors ul { margin: 0; padding-left: 18px; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; font-size: 13px; }
        .topbar a { color: #007bff; font-weight: bold; text-decoration: none; }
        .topbar a:hover { text-decoration: underline; }
        .hint { color: #888; font-size: 12px; margin-top: 4px; }
        .chip-row { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
        .chip { background: #e9ecef; color: #333; padding: 6px 10px; font-size: 12px; border-radius: 14px; font-weight: bold; }
        .chip:hover { background: #dee2e6; }
        .chip.on { background: #007bff; color: white; }
        .sample { background: #f8f9fa; border: 1px dashed #adb5bd; border-radius: 5px; padding: 8px 10px; font-family: Consolas, monospace; font-size: 12px; word-break: break-all; margin-top: 8px; }
        .format-list { margin: 6px 0 0; padding-left: 18px; font-size: 12px; color: #555; }
        .empty { color: #888; font-size: 13px; }
        code { font-family: Consolas, monospace; font-size: 12px; word-break: break-all; }
        .actions { display: flex; gap: 6px; white-space: nowrap; }
        .count-badge { background: #eef5ff; color: #007bff; border-radius: 10px; padding: 3px 8px; font-weight: bold; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }

        /* Ikon SVG inline (Heroicons) */
        .ico { width: 1.05em; height: 1.05em; vertical-align: -0.15em; flex: none; }
        .btn-ico { display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
        .ico-title { display: flex; align-items: center; gap: 7px; }
        .lbl { display: flex; align-items: center; gap: 6px; }
        .error-head { display: flex; align-items: center; gap: 6px; font-weight: bold; margin-bottom: 6px; }
        .topbar span, .topbar a { display: inline-flex; align-items: center; gap: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="ico-title"><x-icon name="building-storefront" /> Kelola Supplier</h2>

    <div class="topbar">
        <span><x-icon name="information-circle" /> Delimiter &amp; urutan barcode dipakai tiap kali PO supplier ini discan.</span>
        <a href="{{ route('scanner.index') }}"><x-icon name="arrow-left" /> Kembali ke Scanner</a>
    </div>

    <section>
        <h3 id="formTitle" class="ico-title"><x-icon name="plus" /> <span>Tambah Supplier</span></h3>
        <form id="supplierForm" novalidate>
            <label><span class="lbl"><x-icon name="building-storefront" /> Nama Supplier</span>
                <input type="text" name="name" maxlength="100" placeholder="PT Sinar Baja" required>
            </label>
            <label><span class="lbl"><x-icon name="tag" /> Kode</span>
                <input type="text" name="code" maxlength="20" placeholder="Opsional, mis. SB">
            </label>
            <label><span class="lbl"><x-icon name="code-bracket" /> Delimiter</span>
                <input type="text" name="delimiter" maxlength="10" value="-" required>
            </label>
            <div class="chip-row" id="delimiterQuick"></div>
            <p class="hint">Karakter pemisah antar bagian di dalam barcode supplier ini.</p>

            <label style="margin-top: 14px;"><span class="lbl"><x-icon name="qr-code" /> Format Barcode</span>
                <input type="text" name="barcode_format" id="barcode_format" maxlength="255" placeholder="manufacturer,grade,heat_no,coil_no,weight,size,qr_date" required>
            </label>
            <div class="chip-row" id="fieldChips"></div>
            <p class="hint">Klik field untuk menambah/menghapus. Urutan di sini = urutan di barcode.</p>

            <ol class="format-list" id="formatPreview"></ol>
            <div class="sample" id="sampleQr"></div>

            <div id="formErrors" class="form-errors" hidden></div>

            <div style="display:flex; gap:10px; margin-top: 14px;">
                <button type="button" class="btn-cancel btn-ico" id="btnCancel" hidden onclick="resetForm()" style="flex:1;"><x-icon name="x-mark" /> Batal</button>
                <button type="submit" class="btn-save btn-ico" id="btnSubmit" style="flex:2;"><x-icon name="check" /> <span>Simpan Supplier</span></button>
            </div>
        </form>
    </section>

    <h3 class="ico-title" style="margin-top: 22px;"><x-icon name="clipboard-document-list" /> <span>Daftar Supplier (<span id="count">0</span>)</span></h3>
    <table id="supplierTable">
        <thead>
            <tr>
                <th><span class="lbl"><x-icon name="building-storefront" /> Nama</span></th>
                <th><span class="lbl"><x-icon name="tag" /> Kode</span></th>
                <th><span class="lbl"><x-icon name="code-bracket" /> Delimiter</span></th>
                <th><span class="lbl"><x-icon name="qr-code" /> Format Barcode</span></th>
                <th><span class="lbl"><x-icon name="clipboard-document-list" /> Arrival</span></th>
                <th><span class="lbl"><x-icon name="wrench" /> Aksi</span></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <p id="emptyState" class="empty" hidden><x-icon name="inbox" /> Belum ada supplier. Tambahkan lewat form di atas.</p>
</div>

<script>
const SUPPLIERS_URL = @json(route('suppliers.index'));
const CSRF_TOKEN = @json(csrf_token());
const ICONS = @json(\App\View\Components\Icon::map());
const JSON_HEADERS = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': CSRF_TOKEN
};
const FIELDS = @json($fields);

// Ikon SVG inline (Heroicons) untuk elemen yang dibuat lewat JavaScript
function iconSvg(name) {
    const markup = ICONS[name];

    if (!markup) {
        return '';
    }

    return '<svg class="ico" width="18" height="18" viewBox="0 0 24 24" fill="none"'
        + ' stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"'
        + ' aria-hidden="true" focusable="false">' + markup + '</svg>';
}

// Ganti judul form beserta ikonnya
function setFormTitle(message, iconName) {
    const title = document.getElementById('formTitle');
    title.innerHTML = iconSvg(iconName) + '<span></span>';
    title.querySelector('span').innerText = message;
}

// Ganti label tombol sambil ikonnya tetap ada
function setButtonLabel(button, message, iconName) {
    button.innerHTML = iconSvg(iconName);

    const label = document.createElement('span');
    label.innerText = message;

    button.appendChild(label);
}

// Judul peringatan isian yang belum valid
function appendErrorHead(box, message) {
    const head = document.createElement('div');
    head.className = 'error-head';
    head.innerHTML = iconSvg('exclamation-circle');

    const label = document.createElement('span');
    label.innerText = message;

    head.appendChild(label);
    box.appendChild(head);
}

// Nilai contoh tiap field, dipakai untuk pratinjau barcode
const SAMPLES = {
    manufacturer: 'Nippon Steel',
    grade: 'SWRH82B',
    heat_no: '12345678',
    coil_no: '87654321',
    weight: '2.369',
    size: '5.5',
    qr_date: '2026-11-01'
};

const DELIMITER_PRESETS = ['-', '|', ';', '#', '~', ','];

let suppliers = @json($suppliers);
let editingId = null;

init();

function init() {
    renderTable();
    renderDelimiterPresets();
    renderFieldChips();
    updateFormatPreview();
}

/* ================= Form ================= */

document.getElementById('supplierForm').addEventListener('submit', function (event) {
    event.preventDefault();
    saveSupplier();
});

async function saveSupplier() {
    const form = document.getElementById('supplierForm');

    if (!form.reportValidity()) {
        return;
    }

    const payload = {
        name: form.name.value.trim(),
        code: form.code.value.trim(),
        delimiter: form.delimiter.value,
        barcode_format: form.barcode_format.value
    };

    const response = await fetch(editingId ? SUPPLIERS_URL + '/' + editingId : SUPPLIERS_URL, {
        method: editingId ? 'PUT' : 'POST',
        headers: JSON_HEADERS,
        body: JSON.stringify(payload)
    });

    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        showErrors(error.errors || { message: [error.message || 'Gagal menyimpan supplier.'] });
        return;
    }

    const supplier = await response.json();

    if (editingId) {
        suppliers = suppliers.map(item => item.id === supplier.id ? supplier : item);
    } else {
        suppliers.push(supplier);
    }

    resetForm();
    renderTable();
}

function editSupplier(id) {
    const supplier = suppliers.find(item => item.id === id);

    if (!supplier) {
        return;
    }

    const form = document.getElementById('supplierForm');
    editingId = id;
    form.name.value = supplier.name;
    form.code.value = supplier.code || '';
    form.delimiter.value = supplier.delimiter;
    form.barcode_format.value = supplier.barcode_format;

    setFormTitle('Ubah Supplier', 'pencil-square');
    setButtonLabel(document.getElementById('btnSubmit'), 'Simpan Perubahan', 'check');
    document.getElementById('btnCancel').hidden = false;
    hideErrors();
    updateFormatPreview();

    window.scrollTo({ top: 0, behavior: 'smooth' });
    form.name.focus();
}

function resetForm() {
    const form = document.getElementById('supplierForm');
    editingId = null;
    form.reset();
    form.delimiter.value = '-';
    setFormTitle('Tambah Supplier', 'plus');
    setButtonLabel(document.getElementById('btnSubmit'), 'Simpan Supplier', 'check');
    document.getElementById('btnCancel').hidden = true;
    hideErrors();
    updateFormatPreview();
}

async function deleteSupplier(id) {
    const supplier = suppliers.find(item => item.id === id);

    if (!supplier || !confirm('Hapus supplier ' + supplier.name + '?')) {
        return;
    }

    const response = await fetch(SUPPLIERS_URL + '/' + id, {
        method: 'DELETE',
        headers: JSON_HEADERS
    });

    const result = await response.json().catch(() => ({}));

    if (!response.ok) {
        alert(result.message || 'Gagal menghapus supplier.');
        return;
    }

    suppliers = suppliers.filter(item => item.id !== id);

    if (editingId === id) {
        resetForm();
    }

    renderTable();
}

function showErrors(errors) {
    const box = document.getElementById('formErrors');
    box.innerHTML = '';
    appendErrorHead(box, 'Periksa isian berikut');

    const list = document.createElement('ul');

    Object.values(errors).flat().forEach(message => {
        const item = document.createElement('li');
        item.innerText = message;
        list.appendChild(item);
    });

    box.appendChild(list);
    box.hidden = false;
}

function hideErrors() {
    const box = document.getElementById('formErrors');
    box.innerHTML = '';
    box.hidden = true;
}

/* ================= Format Barcode ================= */

function currentFormat() {
    return document.getElementById('barcode_format').value
        .split(',')
        .map(field => field.trim())
        .filter(Boolean);
}

function renderDelimiterPresets() {
    const box = document.getElementById('delimiterQuick');
    box.innerHTML = '';

    DELIMITER_PRESETS.forEach(delimiter => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'chip';
        button.innerText = delimiter;
        button.title = 'Pakai "' + delimiter + '" sebagai delimiter';
        button.onclick = () => {
            document.getElementById('supplierForm').delimiter.value = delimiter;
            updateFormatPreview();
        };
        box.appendChild(button);
    });
}

function renderFieldChips() {
    const box = document.getElementById('fieldChips');
    box.innerHTML = '';

    FIELDS.forEach(field => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'chip';
        button.dataset.field = field;
        button.innerText = field;
        button.title = 'Tambah/hapus ' + field + ' dari format barcode';
        button.onclick = () => toggleField(field);
        box.appendChild(button);
    });
}

function toggleField(field) {
    const input = document.getElementById('barcode_format');
    const fields = currentFormat();

    input.value = fields.includes(field)
        ? fields.filter(item => item !== field).join(',')
        : fields.concat(field).join(',');

    updateFormatPreview();
}

function updateFormatPreview() {
    const fields = currentFormat();
    const delimiter = document.getElementById('supplierForm').delimiter.value || '-';
    const list = document.getElementById('formatPreview');
    list.innerHTML = '';

    fields.forEach((field, index) => {
        const item = document.createElement('li');
        item.innerText = field;
        list.appendChild(item);
    });

    document.getElementById('sampleQr').innerText = fields.length > 0
        ? 'Contoh barcode: ' + fields.map(field => SAMPLES[field] || field).join(delimiter)
        : 'Format belum lengkap.';

    document.querySelectorAll('#fieldChips .chip').forEach(chip => {
        chip.classList.toggle('on', fields.includes(chip.dataset.field));
    });
}

document.getElementById('barcode_format').addEventListener('input', updateFormatPreview);
document.getElementById('supplierForm').delimiter.addEventListener('input', updateFormatPreview);

/* ================= Tabel ================= */

function renderTable() {
    const tbody = document.querySelector('#supplierTable tbody');
    tbody.innerHTML = '';

    suppliers.forEach(supplier => {
        const row = document.createElement('tr');

        const name = document.createElement('td');
        const strong = document.createElement('strong');
        strong.innerText = supplier.name;
        name.appendChild(strong);
        row.appendChild(name);

        row.appendChild(cell(supplier.code || '-'));

        const delimiter = document.createElement('td');
        const delimiterCode = document.createElement('code');
        delimiterCode.innerText = supplier.delimiter;
        delimiter.appendChild(delimiterCode);
        row.appendChild(delimiter);

        const format = document.createElement('td');
        const formatCode = document.createElement('code');
        formatCode.innerText = supplier.barcode_format;
        format.appendChild(formatCode);
        row.appendChild(format);

        const arrivals = document.createElement('td');
        const badge = document.createElement('span');
        badge.className = 'count-badge';
        badge.innerHTML = iconSvg('document-text') + ' ' + supplier.arrivals_count + ' PO';
        arrivals.appendChild(badge);
        row.appendChild(arrivals);

        const actions = document.createElement('td');
        actions.className = 'actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'btn-edit';
        editButton.innerHTML = iconSvg('pencil-square') + ' Ubah';
        editButton.onclick = () => editSupplier(supplier.id);

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'btn-delete';
        deleteButton.innerHTML = iconSvg('trash') + ' Hapus';
        deleteButton.onclick = () => deleteSupplier(supplier.id);

        actions.appendChild(editButton);
        actions.appendChild(deleteButton);
        row.appendChild(actions);

        tbody.appendChild(row);
    });

    document.getElementById('count').innerText = suppliers.length;
    document.getElementById('emptyState').hidden = suppliers.length > 0;
}

function cell(value) {
    const element = document.createElement('td');
    element.innerText = value == null ? '' : value;

    return element;
}
</script>

</body>
</html>
