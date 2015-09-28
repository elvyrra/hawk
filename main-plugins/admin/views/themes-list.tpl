<div class="row">
	<div class="col-md-2">
		{button class="btn-success btn-block" icon="plus" label="{Lang::get('admin.theme-create-btn')}" href="{Router::getUri('create-theme')}" target="dialog"}

		{widget class="Hawk\Plugins\Admin\ImportThemeWidget"}	
	</div>

	<div class="col-md-10">		
		<div class="row">					
			{foreach($themes as $name => $theme)}
				<div class="theme-item box pull-left {{ $name === $selectedTheme->getName() ? 'bg-success' : '' }}">
					<div class="box-content">
						<h4>{{ $theme->getTitle() }}</h4>
						<span class="icons">
							{if($name != $selectedTheme->getName())}<i class="icon icon-check text-success select-theme icon-2x" data-theme="{{ $name }}" title="{text key='admin.theme-select-theme'}"></i>{/if}
							{if($theme->isRemovable())}<i class="icon icon-close text-danger delete-theme icon-2x" data-theme="{{ $name }}" title="{text key='admin.theme-delete-theme'}"></i>{/if}
						</span>		
						{if(is_file($theme->getPreviewFilename()))}
							<img src="{{ $theme->getPreviewUrl() }}" class="theme-preview" alt="Theme preview : {{ $theme->getTitle() }}"/>
						{else}
							<span class="icon icon-picture-o icon-5x"></span>
						{/if}
					</div>
				</div>
			{/foreach}
		</div>
	</div>
</div>