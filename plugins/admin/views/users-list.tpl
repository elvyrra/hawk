<div class="row">
	<div class="col-xs-12 col-sm-3 col-lg-2">
		{widget class="Hawk\Plugins\Admin\UserFilterWidget"}
	</div>
	<div class="col-xs-12 col-sm-9 col-lg-10">		
		{{ $list }}		
	</div>
</div>

<style>
	.user-profile-detail{
		list-style-type : none;
		margin-top: 7px;
		padding-left: 5px;
	}

	.profile-image{
		width: 50px;
		height: 50px;
		background-size: cover;
	}
</style>