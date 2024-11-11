@php
   $headsPeriode = [
      ['label' => 'No', 'width' => 5],
      ['label' => 'Periode', 'width' => 8]
   ];

   $configPeriode = [
      'order' => [[0, 'desc']],
      'language' => ['url' => '/json/datatables-id.json'],
      'columns' => [null, null],
   ];
@endphp

<x-adminlte-modal id="tambahPeriode" title="Tambah Periode Sempro" theme="blue" size='lg'>
   <div class="modal-body">
      <div>
         <form method="POST" id="tambahPeriodeFormPendaftaran" class="d-flex flex-row justify-content-center align-items-center w-100">
            @csrf
            @php
               $configPeriode = [
                  'locale' => 'id',
                  'format' => 'MMMM YYYY',
               ];
               $configDate = [
                  'locale' => 'id',
                  'format' => 'DD MMMM YYYY',
               ];
            @endphp
            <div class="mr-4">
               <label for="periodeSempro">Periode Sempro</label>
               <x-adminlte-input-date id="periodeSempro" name="periodeSempro" :config="$configPeriode" placeholder="Tambah Periode Sempro" autocomplete="off">
                  <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                        <i class="fas fa-calendar"></i>
                     </div>
                  </x-slot>
               </x-adminlte-input-date>
            </div>
            <div class="mr-4">
               <label for="tanggalSempro">Tanggal Sempro <span class="text-grey">(opsional)</span></label>
               <x-adminlte-input-date id="tanggalSempro" name="tanggalSempro" :config="$configDate" placeholder="Tambah Tanggal Sempro" autocomplete="off">
                  <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-success">
                        <i class="fas fa-calendar-alt"></i>
                     </div>
                  </x-slot>
               </x-adminlte-input-date>
            </div>
            <button type="submit" id="submitPeriode" class="btn btn-success mt-3">Tambah</button>
         </form>
         <div style="max-height: 500px !important; overflow-y: auto !important;" class="d-flex">
            <table class="table table-bordered table-striped table-hover">
               <thead>
                  <tr>
                     <th scope="col">No</th>
                     <th scope="col">Periode</th>
                     <th scope="col">Tanggal</th>
                  </tr>
               </thead>
               <tbody class="table-group-divider">
                  @if($waktuSempro->isEmpty())
                     <tr>
                        <th scope="row" colspan="3">Belum ada data</th>
                     </tr>
                  @else
                     @foreach($waktuSempro as $index => $data)
                        <tr>
                           <th scope="row">{{ $index + 1 }}</th>
                           <th>{{ $data->periode }}</th>
                           @if ($data->tanggal)
                              <th>
                                 <div class="input-group date datetimepicker" id="datetimepicker{{ $data->id }}" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker{{ $data->id }}" data-toggle="datetimepicker" value="{{ \Carbon\Carbon::parse($data->tanggal)->locale('id')->isoFormat('D MMMM Y') }}"/>
                                 </div>
                              </th>
                           @else
                              <th>
                                 <div class="input-group date datetimepicker" id="datetimepicker{{ $data->id }}" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker{{ $data->id }}" data-toggle="datetimepicker"/>
                                 </div>
                                 {{-- <input type="text" class="form-control datetimepicker-input" id="datetimepicker5" data-toggle="datetimepicker" data-target="#datetimepicker5"/> --}}
                                 {{-- <input type="text" class="datetimepicker-input" id="tanggalSempro-{{ $index }}" name="tanggalSempro[{{ $index }}]" class="tanggalSempro" data-toggle="datetimepicker" data-target="tanggalSempro-{{ $index }}" data-index="{{ $index }}"/> --}}
                                 {{-- <x-adminlte-input-date id="tanggalSempro-{{ $index }}" name="tanggalSempro[{{ $index }}]" :config="$configTanggalSempro" autocomplete="off" class="tanggalSempro" data-index="{{ $index }}"></x-adminlte-input-date> --}}
                              </th>
                           @endif
                        </tr>
                     @endforeach
                  @endif
               </tbody>
            </table>
         </div>
      </div>
      <x-slot name="footerSlot" class="modal-footer">
         <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
      </x-slot>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function() {
      $('#tambahPeriode').on('show.bs.modal', function(event) {
         let button = $(event.relatedTarget);
         let rowId = button.data('row-id');

         let addPeriodeSempro = "{{ route('store.periode.sempro') }}";

         let form = $('#tambahPeriodeFormPendaftaran')

         form.on('keypress', function(event) {
            // Check if the pressed key is Enter (key code 13)
            if (event.which === 13) {
                  // Prevent the default action of the Enter key (e.g., form submission)
                  event.preventDefault();
                  // Submit the form
                  form.attr('action', addPeriodeSempro);
                  form.submit();
            }
         });

         $('#submitPeriode').on('click', function() {
            form.attr('action', addPeriodeSempro);
            form.submit();
         });
      });

      $('.datetimepicker').each(function() {
            // Exclude datetimepickers with specific IDs
            if (!$(this).is('#periodeSempro, #tanggalSempro')) {
                $(this).datetimepicker({
                    format: 'DD MMMM YYYY'
                });
            }
        });

      $('.datetimepicker').on('hide.datetimepicker', function(e) {
         if ($(this).is('#periodeSempro, #tanggalSempro')) {
            return; // Skip AJAX request for specified IDs
         }
         // Get the data-index attribute
         let index = $(this).attr('id').replace('datetimepicker', '');
         // Get the selected date
         let selectedDate = e.date.format('YYYY-MM-DD');

         // API endpoint (replace with your actual endpoint)
         let apiEndpoint = '/api/seminar_proposal/daftar/periode_sempro';

         // Data to send to the server
         let data = {
            indexDate: index,
            tanggalPeriodeUpdate: selectedDate,
         };

         // AJAX request
         $.ajax({
            url: apiEndpoint,
            method: 'PUT',
            data: data,
            headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                  console.log('Date updated successfully:', response);
            },
            error: function(xhr, status, error) {
                  console.error('Error updating date:', error);
            }
         });
      });
   });
</script>
@endpush
