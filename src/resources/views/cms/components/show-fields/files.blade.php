<div class="py-2">
	<label class="mb-3"><b>{{ $label }}</b></label>
	<div class="">
		@if ($files)
			@foreach(json_decode($files) as $file)
				<a href="{{ Storage::url($file) }}" target="_blank" class="mr-2"><i class="fa fa-file" aria-hidden="true"></i><span class="btn-sm">view file</span></a>
			@endforeach
		@else
			<p class="m-0">No files</p>
		@endif
	</div>
</div>