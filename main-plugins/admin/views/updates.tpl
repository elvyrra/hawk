{if(!empty($updates['core']))}
	<h3>{text key="admin.update-page-tab-hawk-title"}</h3>
	{text key="admin.update-page-current-hawk-version" version="{HAWK_VERSION}"}
	{button 
		class="btn-warning update-hawk" 
		icon="refresh" 
		label="{Lang::get('admin.update-page-update-hawk-btn', array('version' => end($updates['core'])['version']))}" 
		data-to="{end($updates['core'])['version']}"
	}
{/if}