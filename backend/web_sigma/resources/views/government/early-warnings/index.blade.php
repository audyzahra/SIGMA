@extends('layouts.government')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/early-warnings.css') }}">
@endpush

@section('title', 'Peringatan Dini | SIGMA Pemerintah')

@section('content')
<div class="ew-page">
    <div class="ew-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('government.dashboard') }}">Dashboard</a><span>/</span><span aria-current="page">Peringatan Dini</span></div>
    <header class="ew-heading">
        <div><div class="ew-eyebrow"><span class="ew-live-dot"></span> MONITORING KESELAMATAN</div><h1>Peringatan Dini</h1><p>Terbitkan arahan keselamatan dan pantau peringatan karhutla untuk wilayah terdampak.</p></div>
        <div class="ew-sync" aria-live="polite"><span class="ew-sync-icon">↻</span><span><strong id="warning-live-state">Sinkronisasi dimulai</strong><small>Pembaruan otomatis setiap 30 detik</small></span></div>
    </header>

    @if (session('success'))<div class="ew-alert ew-alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="ew-alert ew-alert-error" role="alert">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="ew-alert ew-alert-error" role="alert"><strong>Periksa kembali data peringatan.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <section class="ew-stats" aria-label="Ringkasan peringatan">
        <article class="ew-stat"><span class="ew-stat-icon ew-icon-active">!</span><div><span class="ew-stat-label">Peringatan aktif</span><strong id="active-warning-count">{{ $activeCount }}</strong><small>Sedang berlaku</small></div></article>
        <article class="ew-stat"><span class="ew-stat-icon ew-icon-high">↑</span><div><span class="ew-stat-label">Tinggi / kritis</span><strong id="high-warning-count">{{ $highCount }}</strong><small>Masih berlaku</small></div></article>
        <article class="ew-stat"><span class="ew-stat-icon ew-icon-time">◷</span><div><span class="ew-stat-label">Segera kedaluwarsa</span><strong id="expiring-warning-count">{{ $expiringCount }}</strong><small>Dalam 6 jam</small></div></article>
        <article class="ew-stat"><span class="ew-stat-icon ew-icon-closed">✓</span><div><span class="ew-stat-label">Peringatan ditutup</span><strong id="closed-warning-count">{{ $closedCount }}</strong><small>Riwayat tersimpan</small></div></article>
    </section>

    <div class="ew-workspace">
        <section class="ew-panel ew-monitor" aria-labelledby="monitor-heading">
            <div class="ew-panel-heading"><div><div class="ew-eyebrow">PUSAT MONITORING</div><h2 id="monitor-heading">Daftar peringatan</h2><p>Periksa status, masa berlaku, dan riwayat perubahan.</p></div><div class="ew-updated">Pembaruan terakhir <strong id="warning-updated-at">—</strong></div></div>
            <div class="ew-toolbar"><div class="ew-tabs" role="tablist" aria-label="Kategori peringatan"><button type="button" class="ew-tab is-selected" role="tab" aria-selected="true" data-warning-tab="all">Semua</button><button type="button" class="ew-tab" role="tab" aria-selected="false" data-warning-tab="active">Aktif</button><button type="button" class="ew-tab" role="tab" aria-selected="false" data-warning-tab="history">Riwayat</button></div><div class="ew-filters"><label class="ew-search"><span aria-hidden="true">⌕</span><input id="warning-search" type="search" placeholder="Cari area atau wilayah" aria-label="Cari area atau wilayah"></label><label class="ew-filter-label"><span class="visually-hidden">Filter tingkat</span><select id="warning-level-filter"><option value="">Semua tingkat</option><option value="critical">Kritis</option><option value="warning">Peringatan</option><option value="watch">Waspada</option><option value="advisory">Imbauan</option></select></label></div></div>
            <div class="ew-feed-error" id="warning-feed-error" hidden role="alert"><span>Data belum berhasil diperbarui.</span><button type="button" id="warning-retry">Coba lagi</button></div>
            <div class="ew-table-wrap"><table class="ew-table"><thead><tr><th>Tingkat / status</th><th>Peringatan</th><th>Sumber</th><th>Diterbitkan</th><th>Masa berlaku</th><th>Aksi</th></tr></thead><tbody id="warning-rows" data-feed-url="{{ route('government.early-warnings.feed', request()->only('page')) }}" data-regions-url="{{ route('government.early-warnings.regions') }}" data-close-url="{{ route('government.early-warnings.close', ['warning' => '__ID__']) }}" data-csrf="{{ csrf_token() }}" data-detail-url="{{ route('government.early-warnings') }}">
                @forelse ($warnings as $warning)
                    @php
                        $isActive = $warning->isCurrentlyActive();
                        $editWarning = [
                            'id' => $warning->id,
                            'incident_id' => $warning->incident_id,
                            'region_id' => $warning->region_id,
                            'region_level' => $warning->region?->level,
                            'region' => $warning->region?->name,
                            'regency_id' => $warning->region?->level === 'district' ? $warning->region?->parent_id : ($warning->region?->level === 'regency' ? $warning->region_id : null),
                            'regency' => $warning->region?->level === 'district' ? $warning->region?->parent?->name : ($warning->region?->level === 'regency' ? $warning->region?->name : null),
                            'province_id' => $warning->region?->level === 'province' ? $warning->region_id : $warning->region?->parent?->parent_id,
                            'province' => $warning->region?->level === 'province' ? $warning->region?->name : $warning->region?->parent?->parent?->name,
                            'level' => $warning->level,
                            'source' => $warning->source,
                            'area' => $warning->area,
                            'reason' => $warning->reason,
                            'instructions' => $warning->instructions,
                            'expires_at' => $warning->expires_at?->format('Y-m-d\\TH:i'),
                            'expires_at_input' => $warning->expires_at?->format('Y-m-d\\TH:i'),
                        ];
                    @endphp
                    <tr data-status="{{ $isActive ? 'active' : ($warning->status === 'active' ? 'expired' : 'closed') }}" data-level="{{ $warning->level }}" data-search="{{ strtolower($warning->area.' '.($warning->region?->name ?? '').' '.$warning->reason) }}">
                        <td data-label="Tingkat / status"><span class="ew-level ew-level-{{ $warning->level }}">{{ ['critical'=>'Kritis','warning'=>'Peringatan','watch'=>'Waspada','advisory'=>'Imbauan'][$warning->level] ?? ucfirst($warning->level) }}</span><span class="ew-status"><i class="ew-status-dot ew-status-{{ $isActive ? 'active' : ($warning->status === 'active' ? 'expired' : 'closed') }}"></i>{{ $isActive ? 'Aktif' : ($warning->status === 'active' ? 'Kedaluwarsa' : 'Ditutup') }}</span><details class="ew-history"><summary>Riwayat ({{ $warning->histories->count() }})</summary>@foreach ($warning->histories as $history)<div>{{ ucfirst($history->action) }} · {{ $history->actor?->name ?? 'Petugas' }} · {{ $history->created_at?->format('d/m/Y H:i') }}</div>@endforeach</details></td>
                        <td data-label="Peringatan"><strong class="ew-row-title">{{ $warning->area }}</strong><span class="ew-row-subtitle">{{ $warning->region?->name ?? 'Wilayah tidak tersedia' }}</span><span class="ew-row-reason">{{ $warning->reason }}</span><details class="ew-history"><summary>Detail &amp; instruksi</summary><div>{{ $warning->instructions }}</div></details></td>
                        <td data-label="Sumber">{{ str($warning->source)->replace('_', ' ')->title() }}</td><td data-label="Diterbitkan">{{ $warning->issued_at?->format('d/m/Y H:i') ?? '—' }}</td><td data-label="Masa berlaku">{{ $warning->expires_at?->format('d/m/Y H:i') ?? 'Tidak ditentukan' }}</td>
                        <td class="ew-actions">@if ($isActive)<button type="button" class="ew-action" data-edit-warning="{{ json_encode($editWarning, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}">Perbarui</button><form method="POST" action="{{ route('government.early-warnings.close', $warning) }}" data-close-form>@csrf @method('PATCH')<button class="ew-action ew-action-close" type="submit">Tutup</button></form>@else<span class="ew-muted">—</span>@endif</td>
                    </tr>
                @empty <tr class="ew-empty-server"><td colspan="6"><div class="ew-empty"><span>⌕</span><strong>Belum ada peringatan</strong><p>Peringatan yang diterbitkan akan muncul di sini.</p></div></td></tr>
                @endforelse
                </tbody></table></div>
            <div class="ew-empty-filter" id="warning-empty-filter" hidden><strong>Tidak ada peringatan yang cocok</strong><span>Ubah pencarian atau filter untuk melihat daftar lainnya.</span></div>
            <div class="ew-pagination">{{ $warnings->links() }}<span>Menampilkan hingga 20 peringatan per halaman</span></div>
        </section>

        <aside class="ew-panel ew-form-panel" aria-labelledby="form-heading">
            <div class="ew-form-header"><div class="ew-eyebrow">PENERBITAN</div><h2 id="form-heading">Terbitkan peringatan</h2><p>Informasi akan disalurkan kepada warga di wilayah yang dipilih.</p></div>
            <form method="POST" action="{{ route('government.early-warnings.store') }}" id="warning-form" novalidate>@csrf<input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}"><input type="hidden" name="warning_id" id="warning_id"><input type="hidden" name="incident_id" id="incident_id">
                <div class="ew-form-section"><div class="ew-section-title"><span>01</span><div><strong>Sumber &amp; wilayah</strong><small>Pilih dasar dan cakupan peringatan</small></div></div>
                    <label class="ew-field">Insiden atau laporan terverifikasi <span class="ew-optional">Opsional</span><select id="incident-select"><option value="">Tidak ditautkan</option>@foreach ($incidents as $incident)<option value="{{ $incident->id }}">#{{ $incident->id }} · {{ $incident->location_description ?: 'Lokasi tidak tersedia' }}@if ($incident->citizenReports->isNotEmpty()) · Laporan #{{ $incident->citizenReports->first()->id }}@endif</option>@endforeach</select><small>Pilih insiden terverifikasi bila tersedia.</small></label>
                    <div class="ew-field">Wilayah SIGMA <span class="ew-required">Wajib</span>
                        <label class="ew-subfield" for="scope-level">Cakupan penerima</label><select id="scope-level"><option value="district">Kecamatan terpilih</option><option value="regency">Kabupaten/kota terpilih</option><option value="province">Provinsi terpilih</option></select>
                        <label class="ew-subfield" for="province-select">Provinsi</label><select id="province-select"><option value="">Pilih provinsi</option>@foreach ($provinces as $province)<option value="{{ $province->id }}">{{ $province->name }}</option>@endforeach</select>
                        <label class="ew-subfield" for="regency-select">Kabupaten / Kota</label><select id="regency-select" disabled><option value="">Pilih kabupaten/kota</option></select>
                        <label class="ew-subfield" for="district-select">Kecamatan</label><select id="district-select" disabled><option value="">Pilih kecamatan</option></select>
                        <small id="region-help">Pilih wilayah secara berurutan. Nama desa/kelurahan diisi manual pada area terdampak.</small>
                    </div><input type="hidden" name="region_id" id="region_id"><div class="ew-region-selected" id="region-selected" hidden></div><div id="region-picker-status" class="ew-inline-message" aria-live="polite"></div>
                </div>
                <div class="ew-form-section"><div class="ew-section-title"><span>02</span><div><strong>Tingkat bahaya</strong><small>Sesuaikan dengan kondisi yang terverifikasi</small></div></div><label class="ew-field">Tingkat peringatan <span class="ew-required">Wajib</span><select name="level" id="level" required><option value="advisory">Imbauan</option><option value="watch">Waspada</option><option value="warning">Peringatan</option><option value="critical">Kritis</option></select><small id="level-description">Informasi pencegahan dan kesiapsiagaan.</small></label></div>
                <div class="ew-form-section"><div class="ew-section-title"><span>03</span><div><strong>Informasi peringatan</strong><small>Jelaskan dampak dan tindakan yang diperlukan</small></div></div>
                    <div class="ew-field-grid"><label class="ew-field">Sumber <span class="ew-required">Wajib</span><select name="source" id="source" required><option value="citizen_report">Laporan masyarakat</option><option value="confirmed_incident">Insiden terkonfirmasi</option><option value="government">Penilaian pemerintah</option></select></label><label class="ew-field">Desa / Kelurahan atau area terdampak <span class="ew-required">Wajib</span><input name="area" id="area" maxlength="255" required placeholder="Ketik nama desa/kelurahan atau area"></label></div>
                    <label class="ew-field">Alasan penerbitan <span class="ew-required">Wajib</span><textarea name="reason" id="reason" rows="3" maxlength="2000" required placeholder="Jelaskan kondisi atau potensi bahaya yang teridentifikasi"></textarea></label>
                    <label class="ew-field">Instruksi keselamatan <span class="ew-required">Wajib</span><textarea name="instructions" id="instructions" rows="3" maxlength="2000" required placeholder="Tuliskan langkah keselamatan yang perlu dilakukan warga"></textarea></label>
                    <label class="ew-field">Berlaku sampai <span class="ew-optional">Opsional</span><input type="datetime-local" name="expires_at" id="expires_at"><small>Kosongkan untuk masa berlaku 24 jam.</small></label>
                </div>
                <div class="ew-form-actions"><button class="ew-submit" type="submit" id="warning-submit">Terbitkan peringatan</button><button class="ew-reset" type="reset" id="warning-reset">Kosongkan</button></div>
            </form>
        </aside>
    </div>
</div>
<script>
(() => {
    const rows = document.getElementById('warning-rows');
    const state = document.getElementById('warning-live-state');
    const form = document.getElementById('warning-form');
    const search = document.getElementById('warning-search');
    const levelFilter = document.getElementById('warning-level-filter');
    const emptyFilter = document.getElementById('warning-empty-filter');
    const feedError = document.getElementById('warning-feed-error');
    const regionId = document.getElementById('region_id');
    const provinceSelect = document.getElementById('province-select');
    const regencySelect = document.getElementById('regency-select');
    const districtSelect = document.getElementById('district-select');
    const scopeSelect = document.getElementById('scope-level');
    const regionPickerStatus = document.getElementById('region-picker-status');
    const regionSelected = document.getElementById('region-selected');
    let tab = 'all', pending = false, submitting = false, regionRequest = 0;
    const levelNames = {critical:'Kritis',warning:'Peringatan',watch:'Waspada',advisory:'Imbauan'};
    const levelDescriptions = {critical:'Bahaya segera; warga perlu mengikuti instruksi keselamatan.',warning:'Potensi bahaya tinggi; tingkatkan kewaspadaan dan kesiapsiagaan.',watch:'Waspadai perkembangan kondisi di wilayah Anda.',advisory:'Informasi pencegahan dan kesiapsiagaan.'};
    const el = (tag, text, cls) => { const node = document.createElement(tag); node.textContent = text ?? ''; if (cls) node.className = cls; return node; };
    const niceDate = value => value ? new Date(value).toLocaleString('id-ID', {dateStyle:'medium',timeStyle:'short'}) : 'Tidak ditentukan';
    function filterRows() {
        let visible = 0;
        rows.querySelectorAll('tr[data-status]').forEach(row => {
            const matchesTab = tab === 'all' || (tab === 'active' ? row.dataset.status === 'active' : row.dataset.status !== 'active');
            const matchesLevel = !levelFilter.value || row.dataset.level === levelFilter.value;
            const matchesSearch = !search.value.trim() || row.dataset.search.includes(search.value.trim().toLocaleLowerCase('id-ID'));
            row.hidden = !(matchesTab && matchesLevel && matchesSearch);
            if (!row.hidden) visible++;
        });
        emptyFilter.hidden = visible > 0 || !rows.querySelector('tr[data-status]');
    }
    document.querySelectorAll('[data-warning-tab]').forEach(button => button.addEventListener('click', () => {
        tab = button.dataset.warningTab;
        document.querySelectorAll('[data-warning-tab]').forEach(item => { const active = item === button; item.classList.toggle('is-selected', active); item.setAttribute('aria-selected', active ? 'true' : 'false'); });
        filterRows();
    }));
    search.addEventListener('input', filterRows); levelFilter.addEventListener('change', filterRows);
    document.getElementById('level').addEventListener('change', event => { document.getElementById('level-description').textContent = levelDescriptions[event.target.value]; });
    const syncIncidentRequirement = () => { const source = document.getElementById('source').value; const required = source === 'citizen_report' || source === 'confirmed_incident'; document.getElementById('incident-select').required = required; document.getElementById('incident-select').setCustomValidity(required && !document.getElementById('incident-select').value ? 'Pilih insiden yang sesuai dengan sumber peringatan.' : ''); };
    document.getElementById('source').addEventListener('change', syncIncidentRequirement);
    const clearOptions = (select, placeholder) => { select.replaceChildren(new Option(placeholder, '')); select.value = ''; };
    function syncRegionScope() {
        const level=scopeSelect.value, select=level==='province'?provinceSelect:level==='regency'?regencySelect:districtSelect;
        regionId.value=select.value;
        const labels={province:'Provinsi',regency:'Kabupaten/kota',district:'Kecamatan'};
        const path=level==='province'?[provinceSelect.selectedOptions[0]?.text]:level==='regency'?[provinceSelect.selectedOptions[0]?.text,regencySelect.selectedOptions[0]?.text]:[provinceSelect.selectedOptions[0]?.text,regencySelect.selectedOptions[0]?.text,districtSelect.selectedOptions[0]?.text];
        regionSelected.textContent=regionId.value?`Cakupan ${labels[level]}: ${path.filter(Boolean).join(' · ')}`:''; regionSelected.hidden=!regionId.value;
    }
    async function loadChildRegions(parentId, level, select) {
        const requestId = ++regionRequest; select.disabled = true; regionPickerStatus.textContent = 'Memuat daftar wilayah…';
        try {
            const response = await fetch(`${rows.dataset.regionsUrl}?parent_id=${encodeURIComponent(parentId)}&level=${level}`, {headers:{Accept:'application/json'}});
            if (!response.ok) throw new Error();
            const payload = await response.json(); if (requestId !== regionRequest) return;
            for (const region of payload.regions) select.add(new Option(region.name, region.id));
            select.disabled = payload.regions.length === 0;
            regionPickerStatus.textContent = payload.regions.length ? '' : 'Data wilayah pada tingkat ini belum tersedia.';
        } catch (_) { if (requestId === regionRequest) { select.disabled = true; regionPickerStatus.textContent = 'Daftar wilayah gagal dimuat. Pilih ulang wilayah sebelumnya untuk mencoba lagi.'; } }
    }
    provinceSelect.addEventListener('change', () => {
        regionId.value=''; regionSelected.hidden=true; clearOptions(regencySelect,'Pilih kabupaten/kota'); clearOptions(districtSelect,'Pilih kecamatan'); districtSelect.disabled=true;
        if (provinceSelect.value) loadChildRegions(provinceSelect.value,'regency',regencySelect); else regencySelect.disabled=true;
    });
    regencySelect.addEventListener('change', () => {
        regionId.value=''; regionSelected.hidden=true; clearOptions(districtSelect,'Pilih kecamatan');
        if (regencySelect.value) loadChildRegions(regencySelect.value,'district',districtSelect); else districtSelect.disabled=true;
    });
    districtSelect.addEventListener('change', () => {
        syncRegionScope();
    });
    provinceSelect.addEventListener('change',syncRegionScope); regencySelect.addEventListener('change',syncRegionScope); scopeSelect.addEventListener('change',syncRegionScope);
    function setForm(data) {
        form.reset(); document.getElementById('warning_id').value = data.id || ''; document.getElementById('incident_id').value = data.incident_id || ''; document.getElementById('incident-select').value = data.incident_id || '';
        document.getElementById('level').value = data.level || 'advisory'; document.getElementById('level-description').textContent = levelDescriptions[data.level || 'advisory'];
        for (const key of ['source','area','reason','instructions','expires_at']) { const value=key==='expires_at' ? (data.expires_at_input ?? data.expires_at) : data[key]; if (value != null) document.getElementById(key).value = value; }
        scopeSelect.value=data.region_level || 'district'; regionId.value = data.region_id || ''; provinceSelect.value=data.province_id || ''; clearOptions(regencySelect,'Pilih kabupaten/kota'); clearOptions(districtSelect,'Pilih kecamatan'); regencySelect.disabled=true; districtSelect.disabled=true;
        if (data.province_id) { loadChildRegions(data.province_id,'regency',regencySelect).then(async () => { regencySelect.value=data.regency_id || ''; if (data.regency_id && data.region_level === 'district') { await loadChildRegions(data.regency_id,'district',districtSelect); districtSelect.value=data.region_id || ''; } syncRegionScope(); }); }
        regionSelected.textContent = (data.region_level==='province'?[data.region]:data.region_level==='regency'?[data.province,data.region]:[data.province,data.regency,data.region]).filter(Boolean).join(' · '); regionSelected.hidden = !data.region;
        document.getElementById('form-heading').textContent = data.id ? 'Perbarui peringatan' : 'Terbitkan peringatan'; document.getElementById('warning-submit').textContent = data.id ? 'Simpan pembaruan' : 'Terbitkan peringatan';
        document.querySelector('.ew-form-panel').scrollIntoView({behavior:'smooth',block:'start'});
    }
    document.getElementById('incident-select').addEventListener('change', event => { document.getElementById('incident_id').value = event.target.value; syncIncidentRequirement(); });
    document.getElementById('warning-reset').addEventListener('click', () => window.setTimeout(() => { document.getElementById('warning_id').value=''; document.getElementById('incident_id').value=''; regionId.value=''; regionSelected.hidden=true; scopeSelect.value='district'; clearOptions(regencySelect,'Pilih kabupaten/kota'); clearOptions(districtSelect,'Pilih kecamatan'); regencySelect.disabled=true; districtSelect.disabled=true; regionPickerStatus.textContent=''; document.getElementById('form-heading').textContent='Terbitkan peringatan'; document.getElementById('warning-submit').textContent='Terbitkan peringatan'; document.getElementById('level-description').textContent=levelDescriptions[document.getElementById('level').value]; }, 0));
    form.addEventListener('submit', event => { if (submitting) { event.preventDefault(); return; } syncIncidentRequirement(); syncRegionScope(); if (!form.reportValidity()) { event.preventDefault(); return; } if (!regionId.value) { event.preventDefault(); regionPickerStatus.textContent='Pilih wilayah sesuai cakupan penerima yang dipilih.'; (scopeSelect.value==='province'?provinceSelect:scopeSelect.value==='regency'?regencySelect:districtSelect).focus(); return; } submitting=true; const button=document.getElementById('warning-submit'); button.disabled=true; button.textContent='Menyimpan…'; });
    rows.addEventListener('submit', event => { if (event.target.matches('[data-close-form]') && !window.confirm('Tutup peringatan ini? Warga di wilayah terkait akan menerima pembaruan.')) event.preventDefault(); });
    rows.addEventListener('click', event => { const button=event.target.closest('[data-edit-warning]'); if (!button) return; setForm(JSON.parse(button.dataset.editWarning)); });
    function warningRow(warning) {
        const tr=document.createElement('tr'), stateCell=document.createElement('td'), detail=document.createElement('td'), source=document.createElement('td'), issued=document.createElement('td'), expiry=document.createElement('td'), actions=document.createElement('td');
        tr.dataset.status=warning.status; tr.dataset.level=warning.level; tr.dataset.search=`${warning.area} ${warning.region||''} ${warning.reason}`.toLocaleLowerCase('id-ID');
        stateCell.append(el('span',levelNames[warning.level]||warning.level,'ew-level ew-level-'+warning.level)); const statusLabel=warning.status==='active'?'Aktif':warning.status==='expired'?'Kedaluwarsa':'Ditutup'; const status=el('span',statusLabel,'ew-status'); status.prepend(el('i','','ew-status-dot ew-status-'+(warning.status==='expired'?'expired':warning.status==='closed'?'closed':'active'))); stateCell.append(status);
        detail.append(el('strong',warning.area,'ew-row-title'),el('span',warning.region||'Wilayah tidak tersedia','ew-row-subtitle'),el('span',warning.reason,'ew-row-reason')); const instructions=document.createElement('details'); instructions.className='ew-history'; instructions.append(el('summary','Detail & instruksi')); instructions.append(el('div',warning.instructions||'Instruksi tidak tersedia.')); detail.append(instructions);
        source.textContent=(warning.source||'').replaceAll('_',' ').replace(/\b\w/g, char=>char.toUpperCase()); issued.textContent=niceDate(warning.issued_at); expiry.textContent=niceDate(warning.expires_at);
        if (warning.status==='active') { const edit=el('button','Perbarui','ew-action'); edit.type='button'; edit.dataset.editWarning=JSON.stringify(warning); actions.append(edit); const closeForm=document.createElement('form'); closeForm.method='POST'; closeForm.action=rows.dataset.closeUrl.replace('__ID__',warning.id); closeForm.dataset.closeForm=''; for (const [name,value] of [['_token',rows.dataset.csrf],['_method','PATCH']]) { const input=document.createElement('input'); input.type='hidden'; input.name=name; input.value=value; closeForm.append(input); } const close=el('button','Tutup','ew-action ew-action-close'); close.type='submit'; closeForm.append(close); actions.append(closeForm); }
        stateCell.dataset.label='Tingkat / status'; detail.dataset.label='Peringatan'; source.dataset.label='Sumber'; issued.dataset.label='Diterbitkan'; expiry.dataset.label='Masa berlaku'; actions.dataset.label='Aksi';
        tr.append(stateCell,detail,source,issued,expiry,actions); return tr;
    }
    const refresh = async () => {
        if (pending || document.hidden) return; pending=true; state.textContent='Memperbarui…';
        try { const response=await fetch(rows.dataset.feedUrl,{headers:{Accept:'application/json'}}); if (!response.ok) throw new Error(); const payload=await response.json();
            rows.replaceChildren(); for (const warning of payload.warnings) rows.append(warningRow(warning));
            if (!payload.warnings.length) { const tr=document.createElement('tr'), td=document.createElement('td'), empty=document.createElement('div'); td.colSpan=6; empty.className='ew-empty'; empty.append(el('strong','Belum ada peringatan','ew-empty-title'),el('span','Peringatan yang diterbitkan akan muncul di sini.','ew-empty-copy')); td.append(empty); tr.append(td); rows.append(tr); }
            document.getElementById('active-warning-count').textContent=payload.active_count; document.getElementById('high-warning-count').textContent=payload.high_count; document.getElementById('expiring-warning-count').textContent=payload.expiring_count; document.getElementById('closed-warning-count').textContent=payload.closed_count; document.getElementById('warning-updated-at').textContent=niceDate(payload.updated_at);
            feedError.hidden=true; state.textContent='Terhubung · data diperbarui'; filterRows();
        } catch (_) { feedError.hidden=false; state.textContent='Sinkronisasi tertunda'; } finally { pending=false; }
    };
    document.getElementById('warning-retry').addEventListener('click', refresh); refresh(); window.setInterval(refresh,30000); filterRows();
})();
</script>
@endsection
