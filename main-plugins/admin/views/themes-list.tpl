<div class="row">
	<div class="col-md-2">
		{widget class="Hawk\Plugins\Admin\NewThemeWidget"}
	</div>

	<div class="col-md-10">		
		<div class="row">					
			{foreach($themes as $name => $theme)}
				<div class="theme-item box pull-left {{ $name === $selectedTheme->getName() ? 'bg-success' : '' }}">
					<div class="box-content">
						<h4>{{ $theme->getTitle() }}</h4>
						<span class="icons">
							{if($name != $selectedTheme->getName())}<i class="fa fa-check text-success select-theme fa-2x" data-theme="{{ $name }}" title="{text key='admin.theme-select-theme'}"></i>{/if}
							{if($theme->isRemovable())}<i class="fa fa-close text-danger delete-theme fa-2x" data-theme="{{ $name }}" title="{text key='admin.theme-delete-theme'}"></i>{/if}
						</span>						
						<img src="{{ $theme->getPreviewUrl() }}" class="theme-preview" />					
					</div>
				</div>
			{/foreach}
		</div>
	</div>
</div>