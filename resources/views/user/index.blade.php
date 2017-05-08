@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Users</div>

                <div class="panel-body">
                	<div>
                		@can('add_users')
						    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
						        <i class="glyphicon glyphicon-plus-sign"></i> Create
						    </a>
						@endcan
                	</div>
                    <table class="table table-hover">
                    	<thead>
                    		<tr>
                    			<th>Name</th>
                    			<th>Action</th>
                    		</tr>
                    	</thead>
                    	<tbody>
                    	@forelse($result as $res)
                    		<tr>
                    			<td>{{$res->name}}</td>
                    			<td>
                    				<a href="{{url('user')}}/{{$res->id}}" class="btn btn-primary" title="edit">Edit</a>
                    				<a href="#modal-id" class="btn btn-danger" data-toggle="modal" title="delete">Delete</a>
                    			</td>
                    		</tr>
                    	@empty
                    		No users
                    	@endforelse
                    	</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal for delete --}}
<div class="modal fade" id="modal-id">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Danger</h4>
			</div>
			<div class="modal-body">
				Are you sure to delete this element?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
				<button type="button" class="btn btn-primary">Yes</button>
			</div>
		</div>
	</div>
</div>
@endsection

