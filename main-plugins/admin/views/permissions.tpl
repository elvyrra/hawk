<!-- <div class="row"> -->
	<!-- <div class="col-md-6 col-md-offset-3"> -->
		{{ $fields["valid"] }}
		<table class="table">
			{foreach($permissions as $group => $data)}
				<!-- Plugin name -->
				<tr>
					<th class="bg-primary text-center" colspan="{{ count($roles) + 1 }}">{{ Plugin::get($group)->getDefinition('title') }}</th>
				</tr>

				<!-- Role list -->
				<tr>
					<td></td>
					{foreach($roles as $role)}
						<td>{text key="{'roles.role-' . $role->id . '-label'}"}</td>
					{/foreach}
				</tr>

				{foreach($data as $permission)}
					<tr>
						<td>{text key="{$group . '.permission-name-' . $permission->key}"}</td>
						{foreach($roles as $role)}
							<td> {{ isset($fields["permission-$permission->id-$role->id"]) ? $fields["permission-$permission->id-$role->id"] : '' }} </td>
						{/foreach}
					</tr>
				{/foreach}
			{/foreach}
		</table>
	<!-- </div> -->
<!-- </div> -->

<script type="text/javascript">
	$(".select-all").change(function(){
		var data = $(this).attr('name').split("-");
		var roleId = data[2];
		$("input[name$='\-"+roleId+"']").prop("checked", $(this).is(":checked"));
	});
</script>