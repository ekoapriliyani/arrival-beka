<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} · QR Tag Scanner</title>
    <!-- CSS UI Sederhana -->
    <style>
        body { font-family: Arial, sans-serif; padding: 15px; margin: 0; background-color: #f4f6f9; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-top: 0; }
        h3 { margin-bottom: 8px; font-size: 15px; }
        video { width: 100%; border-radius: 8px; background: #000; display: none; margin-bottom: 10px; }
        .btn-group { display: flex; gap: 10px; margin-bottom: 15px; }
        button { flex: 1; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px; }
        button:disabled { background-color: #adb5bd; color: #f4f6f9; cursor: not-allowed; }
        .btn-scan { background-color: #007bff; color: white; }
        .btn-manual { background-color: #6f42c1; color: white; }
        .btn-export { background-color: #28a745; color: white; }
        .btn-clear { background-color: #dc3545; color: white; }
        .btn-save { background-color: #007bff; color: white; width: 100%; }
        .btn-new { background-color: #fd7e14; color: white; width: 100%; }
        .btn-group button { font-size: 13px; padding: 12px 6px; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; padding: 15px; z-index: 100; }
        .modal-overlay[hidden] { display: none; }
        .modal { background: white; border-radius: 8px; padding: 20px; width: 100%; max-width: 420px; max-height: 90vh; overflow-y: auto; }
        .modal-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 4px; }
        .modal-head h3 { margin: 0; font-size: 16px; }
        .modal-close { flex: none; width: 32px; padding: 6px 0; background: #e9ecef; color: #333; font-size: 16px; line-height: 1; }
        .modal-actions { display: flex; gap: 10px; margin-top: 14px; }
        .modal-actions button { flex: 1; }
        .btn-cancel { background-color: #6c757d; color: white; }
        .source-manual { color: #6f42c1; font-weight: bold; }
        .table-scroll { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-top: 10px; border: 1px solid #ddd; border-radius: 6px; background: #fff; }
        table { width: 100%; min-width: 640px; border-collapse: collapse; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; white-space: nowrap; }
        th { background-color: #f2f2f2; }
        .weight-col { font-weight: bold; color: #d9534f; }
        label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 10px; }
        input, textarea, select { display: block; width: 100%; box-sizing: border-box; margin-top: 4px; padding: 9px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; font-family: inherit; background: white; }
        textarea { min-height: 60px; resize: vertical; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; font-size: 13px; }
        .topbar a { color: #007bff; font-weight: bold; text-decoration: none; }
        .topbar a:hover { text-decoration: underline; }
        .form-errors { background: #fdecea; color: #b3261e; border: 1px solid #f5c6cb; border-radius: 5px; padding: 10px; margin-bottom: 10px; font-size: 13px; }
        .form-errors ul { margin: 0; padding-left: 18px; }
        .summary { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 12px; margin-bottom: 15px; }
        .summary-grid { display: grid; grid-template-columns: 110px 1fr; gap: 4px 8px; font-size: 13px; margin: 0 0 10px; }
        .summary-grid dt { font-weight: bold; color: #555; }
        .summary-grid dd { margin: 0; }
        #poHistory { list-style: none; padding: 0; margin: 0; }
        .history-item { display: flex; justify-content: space-between; align-items: center; gap: 8px; border: 1px solid #e0e0e0; border-radius: 5px; padding: 8px 10px; margin-bottom: 6px; font-size: 13px; }
        .history-item.active { border-color: #007bff; background: #eef5ff; }
        .history-info { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .history-info span { color: #666; font-size: 12px; }
        .history-count { color: #007bff !important; font-weight: bold; }
        .history-title { display: flex; align-items: center; gap: 6px; }
        .history-line { display: flex; align-items: center; gap: 5px; }
        .history-actions { display: flex; gap: 6px; flex: none; }
        .history-actions button { flex: none; padding: 6px 10px; font-size: 12px; }
        .btn-open { background-color: #007bff; color: white; }
        .btn-delete { background-color: #dc3545; color: white; }
        .history-empty { color: #888; font-size: 13px; list-style: none; }
        .hint { color: #888; font-size: 12px; margin-top: 4px; }

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
    <h2 class="ico-title"><x-icon name="qr-code" /> QR Tag Scanner Wire Rod</h2>

    <div class="topbar">
        <span><x-icon name="information-circle" /> Supplier menentukan delimiter &amp; urutan barcode.</span>
        <a href="{{ route('suppliers.index') }}"><x-icon name="cog-6-tooth" /> Kelola Supplier &rarr;</a>
    </div>

    {{-- Form header: wajib diisi sebelum Mulai Scan bisa dijalankan --}}
    <section id="headerForm"@if($activeHeader) hidden @endif>
        <h3 class="ico-title"><x-icon name="clipboard-document-list" /> Header Arrival</h3>
        <form id="poForm" novalidate>
            <label><span class="lbl"><x-icon name="document-text" /> Purchase Order</span>
                <input type="text" name="po_number" maxlength="50" placeholder="PO-00123" required>
            </label>
            <label><span class="lbl"><x-icon name="calendar" /> Tanggal</span>
                <input type="date" name="tanggal" required>
            </label>
            <label><span class="lbl"><x-icon name="cube" /> Item</span>
                <input type="text" name="item" maxlength="100" placeholder="Wire Rod" required>
            </label>
            <label><span class="lbl"><x-icon name="pencil-square" /> Description</span>
                <textarea name="description" maxlength="255" placeholder="Opsional"></textarea>
            </label>
            <label><span class="lbl"><x-icon name="building-storefront" /> Supplier</span>
                <select name="supplier_id" required {{ $suppliers->isEmpty() ? 'disabled' : '' }}>
                    <option value="">{{ $suppliers->isEmpty() ? 'Belum ada supplier' : 'Pilih supplier' }}</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->code ? $supplier->code.' · ' : '' }}{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </label>
            @if($suppliers->isEmpty())
                <p class="hint"><x-icon name="information-circle" /> Belum ada supplier. Tambahkan dulu di halaman
                    <a href="{{ route('suppliers.index') }}">Kelola Supplier</a>.
                </p>
            @endif

            <div id="formErrors" class="form-errors" hidden></div>

            <button type="submit" class="btn-save btn-ico"><x-icon name="check" /> Simpan Header</button>
            <p class="hint">Mulai Scan terbuka setelah header tersimpan.</p>
        </form>
    </section>

    {{-- Ringkasan header aktif --}}
    <section id="headerSummary" class="summary"@if(!$activeHeader) hidden @endif>
        <h3 class="ico-title"><x-icon name="check-badge" /> PO Aktif</h3>
        <dl class="summary-grid">
            <dt><x-icon name="document-text" /> Purchase Order</dt><dd id="sumPo">{{ $activeHeader?->po_number }}</dd>
            <dt><x-icon name="calendar" /> Tanggal</dt><dd id="sumTanggal">{{ $activeHeader?->tanggal?->format('Y-m-d') }}</dd>
            <dt><x-icon name="cube" /> Item</dt><dd id="sumItem">{{ $activeHeader?->item }}</dd>
            <dt><x-icon name="pencil-square" /> Description</dt><dd id="sumDescription">{{ $activeHeader?->description ?: '-' }}</dd>
            <dt><x-icon name="building-storefront" /> Supplier</dt><dd id="sumSupplier">{{ $activeHeader?->supplier?->label() ?? '-' }}</dd>
        </dl>
        <button type="button" class="btn-new btn-ico" onclick="newPo()"><x-icon name="plus" /> PO Baru</button>
    </section>

    <video id="preview"></video>

    <div class="btn-group">
        <button class="btn-scan btn-ico" id="btnScan" onclick="toggleScanner()"@if(!$activeHeader) disabled @endif><x-icon name="camera" /> Mulai Scan</button>
        <button class="btn-manual btn-ico" id="btnManual" onclick="openManualForm()"@if(!$activeHeader) disabled @endif><x-icon name="pencil-square" /> Input Manual</button>
        <button class="btn-export btn-ico" onclick="exportToExcel()"><x-icon name="arrow-down-tray" /> Export Excel</button>
        <button class="btn-clear btn-ico" onclick="clearData()"><x-icon name="trash" /> Clear</button>
    </div>

    <h3 class="ico-title"><x-icon name="table-cells" /> <span>Data Scanned (<span id="count">0</span>)</span></h3>
    <div class="table-scroll">
        <table id="dataTable">
            <thead>
                <tr>
                    <th><span class="lbl"><x-icon name="hashtag" /> No</span></th>
                    <th><span class="lbl"><x-icon name="fire" /> Heat No</span></th>
                    <th><span class="lbl"><x-icon name="circle-stack" /> Coil No</span></th>
                    <th><span class="lbl"><x-icon name="scale" /> Weight (MT)</span></th>
                    <th><span class="lbl"><x-icon name="academic-cap" /> Grade</span></th>
                    <th><span class="lbl"><x-icon name="squares-2x2" /> Size</span></th>
                    <th><span class="lbl"><x-icon name="list-bullet" /> Sumber</span></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <h3 class="ico-title" style="margin-top: 20px;"><x-icon name="clock" /> Riwayat PO</h3>
    <ul id="poHistory"></ul>
</div>

{{-- Modal input manual: cadangan saat QR code tidak terbaca --}}
<div id="manualModal" class="modal-overlay" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="manualTitle">
        <div class="modal-head">
            <h3 id="manualTitle" class="ico-title"><x-icon name="pencil-square" /> <span>Input Manual</span></h3>
            <button type="button" class="modal-close" onclick="closeManualForm()" aria-label="Tutup"><x-icon name="x-mark" /></button>
        </div>
        <p class="hint">Isi data dari label fisik bila QR code tidak terbaca.</p>

        <form id="manualForm" novalidate>
            <label><span class="lbl"><x-icon name="building-office-2" /> Manufacturer</span>
                <input type="text" name="manufacturer" maxlength="100" placeholder="Nippon Steel" required>
            </label>
            <label><span class="lbl"><x-icon name="academic-cap" /> Grade</span>
                <input type="text" name="grade" maxlength="50" placeholder="SWRH82B" required>
            </label>
            <label><span class="lbl"><x-icon name="fire" /> Heat No</span>
                <input type="text" name="heat_no" maxlength="100" placeholder="12345678" required>
            </label>
            <label><span class="lbl"><x-icon name="circle-stack" /> Coil No</span>
                <input type="text" name="coil_no" maxlength="100" placeholder="87654321" required>
            </label>
            <label><span class="lbl"><x-icon name="scale" /> Weight (MT)</span>
                <input type="number" name="weight" min="0" step="any" inputmode="decimal" placeholder="2.369" required>
            </label>
            <label><span class="lbl"><x-icon name="squares-2x2" /> Size (mm)</span>
                <input type="number" name="size" min="0" step="any" inputmode="decimal" placeholder="5.5" required>
            </label>
            <label><span class="lbl"><x-icon name="calendar" /> Tanggal QR</span>
                <input type="date" name="qr_date">
            </label>

            <div id="manualErrors" class="form-errors" hidden></div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel btn-ico" onclick="closeManualForm()"><x-icon name="x-mark" /> Batal</button>
                <button type="submit" class="btn-save btn-ico"><x-icon name="check" /> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Library Scanner & Excel -->
<script src="https://unpkg.com/@zxing/library@latest"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
const ARRIVALS_URL = @json(route('arrivals.index'));
const CSRF_TOKEN = @json(csrf_token());
const ICONS = @json(\App\View\Components\Icon::map());
const JSON_HEADERS = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': CSRF_TOKEN
};

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

let headers = @json($headers);
let activeHeader = @json($activeHeader);
let scannedList = @json($activeDetails);
let pendingScans = JSON.parse(localStorage.getItem('qr_pending_scans') || '[]');
let codeReader = null;
let isScanning = false;

// Tampilkan data tersimpan saat pertama load
init();

function init() {
    renderTable();
    renderHistory();

    if (activeHeader) {
        showSummary();
    } else {
        showForm();
    }

    flushPending();
}

function toggleScanner() {
    if (isScanning) {
        stopScanner();
    } else {
        startScanner();
    }
}

function startScanner() {
    if (!activeHeader) {
        alert('Simpan header PO dulu sebelum mulai scan.');
        return;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Kamera tidak tersedia. Buka situs lewat https:// atau localhost, lalu izinkan akses kamera.');
        return;
    }

    const videoElem = document.getElementById('preview');
    videoElem.style.display = 'block';

    codeReader = new ZXing.BrowserMultiFormatReader();
    codeReader.decodeFromVideoDevice(null, 'preview', (result, err) => {
        if (result) {
            // Bunyi feedback beep singkat
            let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            let osc = audioCtx.createOscillator();
            osc.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.1);

            parseAndSave(result.text);
            stopScanner();
        }
    }).catch(err => {
        console.error(err);
        alert(err && err.name === 'NotAllowedError'
            ? 'Izin kamera ditolak. Klik ikon gembok di address bar, setel Kamera ke "Izinkan", lalu coba lagi.'
            : 'Gagal mengakses kamera: ' + (err && err.message ? err.message : err));
        stopScanner();
    });

    isScanning = true;
    document.getElementById('btnScan').innerHTML = iconSvg('stop') + ' Stop Scan';
}

function stopScanner() {
    if (codeReader) {
        codeReader.reset();
    }
    document.getElementById('preview').style.display = 'none';
    isScanning = false;
    document.getElementById('btnScan').innerHTML = iconSvg('camera') + ' Mulai Scan';
}

/* ================= Header PO ================= */

// Nama supplier aktif, dipakai di ringkasan, riwayat, dan export
function formatSupplier(supplier) {
    if (!supplier) {
        return '-';
    }

    return supplier.code ? supplier.code + ' · ' + supplier.name : supplier.name;
}

function supplierName() {
    return formatSupplier(activeHeader && activeHeader.supplier);
}

function showForm() {
    document.getElementById('headerSummary').hidden = true;
    document.getElementById('headerForm').hidden = false;
    document.getElementById('btnScan').disabled = true;
    document.getElementById('btnManual').disabled = true;
    hideFormErrors();
}

function showSummary() {
    if (!activeHeader) {
        return;
    }

    document.getElementById('headerForm').hidden = true;
    document.getElementById('headerSummary').hidden = false;
    document.getElementById('sumPo').innerText = activeHeader.po_number;
    document.getElementById('sumTanggal').innerText = activeHeader.tanggal;
    document.getElementById('sumItem').innerText = activeHeader.item;
    document.getElementById('sumDescription').innerText = activeHeader.description || '-';
    document.getElementById('sumSupplier').innerText = supplierName();
    document.getElementById('btnScan').disabled = false;
    document.getElementById('btnManual').disabled = false;
    renderHistory();
}

function showFormErrors(errors) {
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

function hideFormErrors() {
    const box = document.getElementById('formErrors');
    box.innerHTML = '';
    box.hidden = true;
}

document.getElementById('poForm').addEventListener('submit', function (event) {
    event.preventDefault();
    saveHeader();
});

async function saveHeader() {
    const form = document.getElementById('poForm');

    if (!form.reportValidity()) {
        return;
    }

    const payload = {
        po_number: form.po_number.value.trim(),
        tanggal: form.tanggal.value,
        item: form.item.value.trim(),
        description: form.description.value.trim(),
        supplier_id: form.supplier_id.value
    };

    if (!payload.supplier_id) {
        showFormErrors({ supplier_id: ['Pilih supplier dulu. Tambahkan di halaman Kelola Supplier.'] });
        return;
    }

    const response = await fetch(ARRIVALS_URL, {
        method: 'POST',
        headers: JSON_HEADERS,
        body: JSON.stringify(payload)
    });

    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        showFormErrors(error.errors || { message: [error.message || 'Gagal menyimpan header.'] });
        return;
    }

    const header = await response.json();
    activeHeader = header;
    headers.unshift(Object.assign({}, header, { details_count: 0 }));
    scannedList = [];

    form.reset();
    renderTable();
    showSummary();
}

function newPo() {
    if (isScanning) {
        alert('Stop scan dulu sebelum ganti PO.');
        return;
    }

    activeHeader = null;
    scannedList = [];
    document.getElementById('poForm').reset();
    renderTable();
    showForm();
}

async function openHeader(id) {
    if (isScanning) {
        alert('Stop scan dulu sebelum ganti PO.');
        return;
    }

    const response = await fetch(ARRIVALS_URL + '/' + id, {
        headers: { 'Accept': 'application/json' }
    });

    if (!response.ok) {
        alert('Gagal memuat data PO.');
        return;
    }

    const header = await response.json();
    activeHeader = header;
    scannedList = header.details || [];

    renderTable();
    showSummary();
    flushPending();
}

async function deleteHeader(id) {
    if (isScanning) {
        alert('Stop scan dulu sebelum menghapus PO.');
        return;
    }

    if (!confirm('Hapus PO beserta seluruh data scan-nya?')) {
        return;
    }

    const response = await fetch(ARRIVALS_URL + '/' + id, {
        method: 'DELETE',
        headers: JSON_HEADERS
    });

    if (!response.ok) {
        alert('Gagal menghapus PO.');
        return;
    }

    headers = headers.filter(header => header.id !== id);
    pendingScans = pendingScans.filter(pending => pending.headerId !== id);
    savePendingScans();

    if (activeHeader && activeHeader.id === id) {
        activeHeader = null;
        scannedList = [];
        renderTable();
        showForm();
    }

    renderHistory();
}

function renderHistory() {
    const list = document.getElementById('poHistory');
    list.innerHTML = '';

    if (headers.length === 0) {
        const empty = document.createElement('li');
        empty.className = 'history-empty';
        empty.innerHTML = iconSvg('inbox') + ' Belum ada PO tersimpan.';
        list.appendChild(empty);
        return;
    }

    headers.forEach(header => {
        const item = document.createElement('li');
        item.className = 'history-item' + (activeHeader && activeHeader.id === header.id ? ' active' : '');

        const info = document.createElement('div');
        info.className = 'history-info';

        const title = document.createElement('strong');
        title.className = 'history-title';
        title.innerHTML = iconSvg('document-text');
        title.appendChild(document.createTextNode(header.po_number));
        info.appendChild(title);

        [
            [header.tanggal, 'calendar'],
            [(header.item || '') + ' · ' + formatSupplier(header.supplier), 'cube']
        ].forEach(([text, iconName]) => {
            const line = document.createElement('span');
            line.className = 'history-line';
            line.innerHTML = iconSvg(iconName);
            line.appendChild(document.createTextNode(text));
            info.appendChild(line);
        });

        const count = document.createElement('span');
        count.className = 'history-count';
        count.innerText = header.details_count + ' scan';
        info.appendChild(count);

        const actions = document.createElement('div');
        actions.className = 'history-actions';

        const openButton = document.createElement('button');
        openButton.type = 'button';
        openButton.className = 'btn-open btn-ico';
        openButton.innerHTML = iconSvg('folder-open') + ' Buka';
        openButton.onclick = () => openHeader(header.id);

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'btn-delete btn-ico';
        deleteButton.innerHTML = iconSvg('trash') + ' Hapus';
        deleteButton.onclick = () => deleteHeader(header.id);

        actions.appendChild(openButton);
        actions.appendChild(deleteButton);

        item.appendChild(info);
        item.appendChild(actions);
        list.appendChild(item);
    });
}

/* ================= Scan ================= */

// Cek duplikat coil pada PO aktif (data tersimpan maupun antrean lokal)
function isDuplicateCoil(coilNo) {
    const coil = String(coilNo || '').trim();

    if (!coil || !activeHeader) {
        return false;
    }

    const inList = scannedList.some(item => String(item.coil_no || '').trim() === coil);
    const inPending = pendingScans.some(item =>
        item.headerId === activeHeader.id
        && String((item.payload && item.payload.coil_no) || '').trim() === coil
    );

    return inList || inPending;
}

// Parsing string QR dan simpan ke database
async function parseAndSave(qrText) {
    if (!activeHeader) {
        alert('Simpan header PO dulu sebelum mulai scan.');
        return;
    }

    const supplier = activeHeader.supplier;

    if (!supplier || !supplier.delimiter || !supplier.barcode_format) {
        alert('Supplier pada PO ini belum punya pengaturan barcode. Buka halaman Kelola Supplier.');
        return;
    }

    // Delimiter dan urutan field mengikuti supplier yang dipilih di PO aktif
    const fields = supplier.barcode_format.split(',').map(field => field.trim()).filter(Boolean);
    const parts = qrText.split(supplier.delimiter);

    if (parts.length < fields.length) {
        alert('Format QR Code tidak valid: diharapkan ' + fields.length
            + ' bagian dipisah "' + supplier.delimiter + '" untuk supplier ' + supplier.name
            + ', yang terbaca ' + parts.length + ' bagian.\n\nTeks terbaca: ' + String(qrText).slice(0, 200));
        return;
    }

    const payload = { source: 'scan' };

    fields.forEach((field, index) => {
        payload[field] = String(parts[index]).trim();
    });

    payload.weight = parseFloat(payload.weight);
    payload.size = parseFloat(payload.size);

    if (!isFinite(payload.weight) || !isFinite(payload.size)) {
        alert("Format QR Code tidak valid: berat atau size bukan angka!");
        return;
    }

    if (payload.qr_date !== undefined && !isDateOnly(payload.qr_date)) {
        payload.qr_date = null;
    }

    if (isDuplicateCoil(payload.coil_no)) {
        alert('Data duplikat: Coil No "' + payload.coil_no + '" sudah ada pada PO ini.');
        return;
    }

    const result = await postDetail(payload);

    if (!result.ok) {
        if (isDuplicateMessage(result.message)) {
            alert('Data duplikat: Coil No "' + payload.coil_no + '" sudah ada pada PO ini.');
            return;
        }

        queuePending(payload);
        return;
    }

    // Data yang gagal tersimpan sebelumnya dicoba ulang lebih dulu
    await flushPending();

    scannedList.push(result.data);
    renderTable();
}

function isDateOnly(value) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return false;
    }

    return !isNaN(Date.parse(value));
}

function isDuplicateMessage(message) {
    const text = String(message || '').toLowerCase();

    return text.includes('duplikat') || text.includes('sudah ada') || text.includes('sudah pernah') || text.includes('unique');
}

// Tanggal hari ini (timezone lokal) dalam format YYYY-MM-DD
function todayDateOnly() {
    const today = new Date();
    const pad = value => String(value).padStart(2, '0');

    return today.getFullYear() + '-' + pad(today.getMonth() + 1) + '-' + pad(today.getDate());
}

/* ================= Input Manual ================= */

// Cadangan input bila QR code tidak terbaca
function openManualForm() {
    if (!activeHeader) {
        alert('Simpan header PO dulu sebelum input manual.');
        return;
    }

    if (isScanning) {
        alert('Stop scan dulu sebelum input manual.');
        return;
    }

    const form = document.getElementById('manualForm');
    form.reset();
    hideManualErrors();

    // Manufacturer mengikuti supplier dari PO yang sedang aktif
    const manufacturerValue = activeHeader.supplier ? activeHeader.supplier.name : '';
    form.manufacturer.value = manufacturerValue;

    // Tanggal QR diisi otomatis hari ini
    form.qr_date.value = todayDateOnly();

    document.getElementById('manualModal').hidden = false;

    // Fokus ke field berikutnya bila manufacturer sudah terisi otomatis
    if (manufacturerValue) {
        form.grade.focus();
    } else {
        form.manufacturer.focus();
    }
}

function closeManualForm() {
    document.getElementById('manualModal').hidden = true;
    hideManualErrors();
}

function showManualErrors(errors) {
    const box = document.getElementById('manualErrors');
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

function hideManualErrors() {
    const box = document.getElementById('manualErrors');
    box.innerHTML = '';
    box.hidden = true;
}

document.getElementById('manualForm').addEventListener('submit', function (event) {
    event.preventDefault();
    saveManualEntry();
});

// Tutup modal saat klik area gelap di luar form atau menekan Esc
document.getElementById('manualModal').addEventListener('click', function (event) {
    if (event.target === this) {
        closeManualForm();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !document.getElementById('manualModal').hidden) {
        closeManualForm();
    }
});

async function saveManualEntry() {
    const form = document.getElementById('manualForm');

    if (!form.reportValidity()) {
        return;
    }

    const payload = {
        manufacturer: form.manufacturer.value.trim(),
        grade: form.grade.value.trim(),
        heat_no: form.heat_no.value.trim(),
        coil_no: form.coil_no.value.trim(),
        weight: parseFloat(form.weight.value),
        size: parseFloat(form.size.value),
        qr_date: form.qr_date.value || null,
        source: 'manual'
    };

    if (isDuplicateCoil(payload.coil_no)) {
        showManualErrors({ coil_no: ['Data duplikat: Coil No "' + payload.coil_no + '" sudah ada pada PO ini.'] });
        return;
    }

    const result = await postDetail(payload);

    if (!result.ok) {
        // Isian tetap tersimpan di form agar bisa diperbaiki
        showManualErrors({ message: [isDuplicateMessage(result.message)
            ? 'Data duplikat: Coil No "' + payload.coil_no + '" sudah ada pada PO ini.'
            : 'Gagal menyimpan data: ' + result.message] });
        return;
    }

    // Data yang gagal tersimpan sebelumnya dicoba ulang lebih dulu
    await flushPending();

    scannedList.push(result.data);
    renderTable();
    closeManualForm();
}

/**
 * Kirim satu payload detail ke server.
 *
 * @returns {Promise<{ok: boolean, data?: object, message?: string}>}
 */
async function postDetail(payload) {
    try {
        const response = await fetch(ARRIVALS_URL + '/' + activeHeader.id + '/details', {
            method: 'POST',
            headers: JSON_HEADERS,
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            const message = error.errors
                ? Object.values(error.errors).flat().join(' ')
                : (error.message || 'server tidak merespons.');

            return { ok: false, message: message };
        }

        return { ok: true, data: await response.json() };
    } catch (err) {
        console.error(err);
        return { ok: false, message: 'Tidak dapat terhubung ke server.' };
    }
}

function queuePending(payload) {
    if (isDuplicateCoil(payload.coil_no)) {
        alert('Data duplikat: Coil No "' + payload.coil_no + '" sudah ada pada PO ini.');
        return;
    }

    pendingScans.push({ headerId: activeHeader.id, payload: payload });
    savePendingScans();
    alert('Scan tidak tersimpan ke server, data disimpan sementara dan akan dikirim ulang otomatis.');
}

function savePendingScans() {
    localStorage.setItem('qr_pending_scans', JSON.stringify(pendingScans));
}

async function flushPending() {
    if (!activeHeader) {
        return;
    }

    const flushed = [];

    while (pendingScans.length > 0 && pendingScans[0].headerId === activeHeader.id) {
        const result = await postDetail(pendingScans[0].payload);

        if (!result.ok) {
            // Duplikat (mis. tersimpan di perangkat lain) dibuang dari antrean
            if (isDuplicateMessage(result.message)) {
                pendingScans.shift();
                savePendingScans();
                continue;
            }

            break;
        }

        pendingScans.shift();
        flushed.push(result.data);
    }

    if (flushed.length > 0) {
        savePendingScans();
        scannedList = flushed.concat(scannedList);
        renderTable();
    }
}

function renderTable() {
    const tbody = document.querySelector('#dataTable tbody');
    tbody.innerHTML = '';

    scannedList.forEach((item, index) => {
        const row = document.createElement('tr');

        [
            String(index + 1),
            item.heat_no,
            item.coil_no,
            String(item.weight),
            item.grade,
            item.size + ' mm',
            item.source === 'manual' ? 'Manual' : 'Scan'
        ].forEach((value, position) => {
            const cell = document.createElement('td');
            cell.innerText = value == null ? '' : value;

            if (position === 3) {
                cell.className = 'weight-col';
            }

            if (position === 6 && item.source === 'manual') {
                cell.className = 'source-manual';
            }

            row.appendChild(cell);
        });

        tbody.appendChild(row);
    });

    document.getElementById('count').innerText = scannedList.length;

    if (activeHeader) {
        const current = headers.find(header => header.id === activeHeader.id);

        if (current) {
            current.details_count = scannedList.length;
        }

        renderHistory();
    }
}

/* ================= Clear & Export ================= */

async function clearData() {
    if (!activeHeader) {
        alert('Belum ada PO aktif.');
        return;
    }

    if (scannedList.length === 0) {
        alert('Belum ada data untuk dihapus!');
        return;
    }

    if (!confirm('Hapus semua data scan PO ini? Header PO tetap tersimpan.')) {
        return;
    }

    const response = await fetch(ARRIVALS_URL + '/' + activeHeader.id + '/details', {
        method: 'DELETE',
        headers: JSON_HEADERS
    });

    if (!response.ok) {
        alert('Gagal menghapus data scan.');
        return;
    }

    scannedList = [];
    renderTable();
}

function exportToExcel() {
    if (!activeHeader) {
        alert('Belum ada PO aktif. Simpan header dulu.');
        return;
    }

    if (scannedList.length === 0) {
        alert("Belum ada data untuk diexport!");
        return;
    }

    // Info header + data scan dalam satu sheet
    const rows = [
        ['Arrival-Beka - Data Arrival'],
        ['Purchase Order', activeHeader.po_number, 'Tanggal', activeHeader.tanggal],
        ['Item', activeHeader.item, 'Supplier', supplierName()],
        ['Description', activeHeader.description || '-'],
        [],
        ['No', 'Manufacturer', 'Grade', 'Heat No', 'Coil No', 'Weight (MT)', 'Size (mm)', 'Date', 'Waktu Scan', 'Sumber']
    ];

    scannedList.forEach((item, index) => {
        rows.push([
            index + 1,
            item.manufacturer,
            item.grade,
            item.heat_no,
            item.coil_no,
            item.weight,
            item.size,
            item.qr_date || '',
            item.scanned_at ? new Date(item.scanned_at).toLocaleString('id-ID') : '',
            item.source === 'manual' ? 'Manual' : 'Scan'
        ]);
    });

    const worksheet = XLSX.utils.aoa_to_sheet(rows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Data Scan");

    const poFile = String(activeHeader.po_number).replace(/[^a-zA-Z0-9_-]/g, '_');

    // Download file .xlsx
    XLSX.writeFile(workbook, `Arrival_${poFile}_${new Date().toISOString().slice(0,10)}.xlsx`);
}
</script>

</body>
</html>
