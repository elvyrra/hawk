<div class="modal-dialog" style="{{ $width ? "width:$width;" : ""}}{{$height ? "height:$height;" : ""}}">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
			<h4 class="modal-title"><i class="fa fa-{{$icon}}"></i>  {{$title}}</h4>
		</div>
		<div class="modal-body">
			{{$page}}
		</div>
	</div>
</div>
