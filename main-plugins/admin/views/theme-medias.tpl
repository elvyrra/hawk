<div class="row">
	<div class="col-md-2">
		{widget class="Hawk\Plugins\Admin\NewMediaWidget"}
	</div>

	<div class="col-md-10">
		{foreach($medias as $catname => $category)}
			<div class="row">
				<h3> <i class="icon icon-{{ $category['icon'] }}"></i> {text key="{'admin.theme-medias-category-' . $catname . '-title'}" }</h3>
				<hr />
				{if(!empty($category['files']))}
					{foreach($category['files'] as $file)}
						<div class="box pull-left theme-media-item">					
							<div class="box-content">
								<span class="icon icon-close text-danger pull-right delete-theme-media pointer icon-lg" data-filename="{{ basename($file['url']) }}"></span>
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