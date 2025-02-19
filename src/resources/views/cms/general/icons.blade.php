@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')

	<div class="container-fluid  px-md-5 mt-5 ">
		<div class="white-card">
			<div class="row">

				<div class="col-12 py-3">
					<h4>Solid Icons </h4>
				</div>
				@foreach( $config['solid_icons'] as $icon)
					<div class="col-2 text-center py-2">
						<i class="fa fa-solid {{ $icon }}" aria-hidden="true"></i>
						<p class="mt-1">fa-solid {{ $icon }}</p>
					</div>
				@endforeach

				<div class="col-12 py-3">
					<h4>Regular Icons </h4>
				</div>
				@foreach(  $config['regular_icons'] as $icon)
					<div class="col-2 text-center py-2">
						<i class="fa fa-regular {{ $icon }}" aria-hidden="true"></i>
						<p class="mt-2">{{ $icon }}</p>
					</div>
				@endforeach


				<div class="col-12 py-3">
					<h4>Brands Icons </h4>
				</div>
				@foreach(  $config['brand_icons'] as $icon)
					<div class="col-2 text-center py-2">
						<i class="fa fa-brands {{ $icon }}" aria-hidden="true"></i>
						<p class="mt-1">{{ $icon }}</p>
					</div>
				@endforeach


				<div class="col-12 mt-3 text-right">
					<p>For more icons you can look for <a href="https://fontawesome.com/search?ic=free" target="_blank">Official Font Awesome icons</a></p>
				</div>
			</div>
		</div>
	</div>

@endsection