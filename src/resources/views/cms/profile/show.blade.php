@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')

<div class="container-fluid px-md-5 mt-5 ">
	<div class="white-card">

		<div class="row mb-3">
			<div class="col-lg-6">
				@include('darpersocms::cms.components.breadcrumb.index', ['title' => 'PROFILE'])
			</div>
			<div class="col-lg-6 text-right">
				<a href="{{ route('admin-profile-edit') }}" class="btn-action lg edit ml-auto"><i class="fa-solid fa-pen"></i></a>
			</div>
		</div>

		@include('darpersocms::cms.components/show-fields/text', ['label' => 'Name', 'value' => request()->get('admin')['name']])

		@include('darpersocms::cms.components/show-fields/image', ['label' => 'Image', 'value' => request()->get('admin')['image'] ? request()->get('admin')['image'] : ''])

	</div>

</div>
@endsection