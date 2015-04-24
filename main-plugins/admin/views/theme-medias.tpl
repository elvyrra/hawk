<div class="row">
	<div class="col-md-2">
		{widget class="NewMediaWidget"}
	</div>

	<div class="col-md-10">
		{foreach($medias as $catname => $category)}
			<div class="row">
				<h3> <i class="fa fa-{{ $category['icon'] }}"></i> {{ Lang::get('admin.theme-medias-category-' . $catname . '-title') }} </h3>
				<hr />
				{if(!empty($category['files']))}
					{foreach($category['files'] as $file)}
						<div class="box pull-left theme-media-item">					
							<div class="box-content">
								<span class="fa fa-close text-danger pull-right delete-theme-media" data-filename="{{ basename($file['url']) }}"></span>
								<div>{{ $file['display']}} </div>
								<input type="text" readonly value="{{ $file['url']}}" title="{{ $file['url']}}" class="theme-media-url"/>
							</div>
						</div>
					{/foreach}
				{else}
					<p class="text-center text-danger">{text key="admin.theme-no-media"}</p>
				{/if}
			</div>
		{/foreach}
	</div>
</div>