@props(['id', 'name', 'prodi', 'count', 'ongoing', 'finished', 'parent' => null, 'exportUrl' => null])

<tr data-toggle="collapse" data-target="#collapse-{{ $id }}" class="accordion-toggle"
    style="cursor: pointer; background-color: #f8f9fa;" {{ $attributes }}>
    <td>
        <i class="fas fa-chevron-down mr-2 text-muted"></i> {{ $name }}
    </td>
    <td>{{ $prodi }}</td>
    <td>{{ $count }} Mahasiswa</td>
    <td class="text-center">{{ $ongoing }}</td>
    <td class="text-center">{{ $finished }}</td>
</tr>

<tr>
    <td colspan="5" class="p-0 border-0">
        <div id="collapse-{{ $id }}" class="collapse bg-light" @if($parent) data-parent="{{ $parent }}" @endif>
            <div class="p-4">
                <h6 class="font-weight-bold mb-3">Daftar Mahasiswa ({{ $count }})</h6>

                <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary btn-sm btn-filter-inner"
                            data-target="#collapse-{{ $id }}" data-filter="all">Semua</button>
                        <button type="button" class="btn btn-secondary btn-sm btn-filter-inner"
                            data-target="#collapse-{{ $id }}" data-filter="ongoing">Ongoing</button>
                        <button type="button" class="btn btn-secondary btn-sm btn-filter-inner"
                            data-target="#collapse-{{ $id }}" data-filter="selesai">Selesai</button>
                    </div>

                    @if ($exportUrl)
                        <a href="{{ $exportUrl }}" class="btn btn-success btn-sm mt-2 mt-sm-0">
                            <i class="fas fa-file-excel mr-1"></i> Download Excel
                        </a>
                    @endif
                </div>

                <table class="table table-sm table-borderless inner-table">
                    <thead style="background-color: #e9ecef;">
                        <tr>
                            <th style="width: 20%">Nama Mahasiswa</th>
                            <th style="width: 15%">NIM</th>
                            <th style="width: 35%">Judul</th>
                            <th style="width: 10%" class="text-center">Bimbingan</th>
                            <th style="width: 20%" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{ $slot }}
                    </tbody>
                </table>
            </div>
        </div>
    </td>
</tr>