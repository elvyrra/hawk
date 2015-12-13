<div class="row">
	<div class="col-md-2">
		{button class="btn-success btn-block" icon="plus" label="{text key='admin.theme-create-btn'}" href="{uri action='create-theme'}" target="dialog"}

		{widget class="Hawk\Plugins\Admin\SearchThemeWidget"}			

	</div>

	<div class="col-md-10">		
		<div class="row themes-list">					
			{foreach($themes as $name => $theme)}
				<div class="theme-item box pull-left {{ $name === $selectedTheme->getName() ? 'bg-success' : '' }}">
					<div class="box-content">
						<div class="theme-item-header">
							<h4>{{ $theme->getTitle() }}</h4>
							<span class="icons">
								{if($name != $selectedTheme->getName())}<i class="icon icon-check text-success select-theme icon-2x pointer" data-theme="{{ $name }}" title="{text key='admin.theme-select-theme'}"></i>{/if}
								{if($theme->isRemovable())}<i class="icon icon-close text-danger delete-theme icon-2x pointer" data-theme="{{ $name }}" title="{text key='admin.theme-delete-theme'}"></i>{/if}
							</span>
						</div>
						<div class="theme-preview">		
							{if(is_file($theme->getPreviewFilename()))}
								<img src="{{ $theme->getPreviewUrl() }}" alt="Theme preview : {{ $theme->getTitle() }}"/>
							{else}
								<span class="icon icon-picture-o icon-5x"></span>
							{/if}
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
</div>