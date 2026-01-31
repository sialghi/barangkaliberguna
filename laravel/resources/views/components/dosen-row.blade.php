@props(['id', 'name', 'prodi', 'count', 'ongoing', 'finished', 'parent' => null])

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

                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-primary btn-sm btn-filter-inner"
                        data-target="#collapse-{{ $id }}" data-filter="all">Semua</button>
                    <button type="button" class="btn btn-secondary btn-sm btn-filter-inner"
                        data-target="#collapse-{{ $id }}" data-filter="selesai">Selesai</button>
                    <button type="button" class="btn btn-secondary btn-sm btn-filter-inner"
                        data-target="#collapse-{{ $id }}" data-filter="ongoing">Ongoing</button>
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