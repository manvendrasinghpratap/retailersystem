@props([
    'showPdf' => true,
    'showCsv' => true,
    'pdfId' => 'downloadPdfBtn',
    'csvId' => 'downloadCsvBtn',
    'pdfClass' => 'downloadPdfClass',
    'csvClass' => 'downloadCsvClass',
    'pdfRoute' => '#',
    'csvRoute' => '#',
    'pdfTitle' => 'Download as PDF',
    'csvTitle' => 'Export CSV',
])

@if($showPdf)
    <x-href-input  
        :title="$pdfTitle" 
        :id="$pdfId" 
        :name="$pdfId"
        href="javascript:void(0)"  
        action="pdf" 
        :class="$pdfClass"
        :data-downloadroutepdf="$pdfRoute"
    >
        <i class="fas fa-file-pdf text-danger mr-1"></i> PDF
    </x-href-input>
@endif

@if($showCsv)
    <x-href-input  
        download 
        :title="$csvTitle" 
        :id="$csvId" 
        name="$csvId" 
        href="javascript:void(0)"  
        action="csv" 
        :class="$csvClass"
        :data-downloadroutepdf="$csvRoute"
    >
        <i class="fas fa-file-csv text-success mr-1"></i> CSV
    </x-href-input>
@endif


{{-- Auto-initialize JS dynamically --}}
@push('scripts')
<script>
$(document).ready(function() {
    @if($showPdf)
        setupPdfDownload('.{{ $pdfClass }}', 'data-downloadroutepdf');
    @endif

    @if($showCsv)
        setupPdfDownload('.{{ $csvClass }}', 'data-downloadroutepdf');
    @endif
});
</script>
@endpush