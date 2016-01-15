<div class="row">
	<div class="col-md-2">
		{button class="btn-success btn-block" icon="plus" label="{text key='admin.theme-create-btn'}" href="{uri action='create-theme'}" target="dialog"}

		{widget class="Hawk\Plugins\Admin\SearchThemeWidget"}
	</div>

	<div class="col-md-10">
		<div class="row themes-list">
			{foreach($themes as $name => $theme)}
				<div class="theme-item box col-sm-3">
					<div class="box-content">
						<div class="theme-item-header">
							<h4>{{ $theme->getTitle() }}</h4>
						</div>
						<div class="theme-preview">
							{if(is_file($theme->getPreviewFilename()))}
								<img src="{{ $theme->getPreviewUrl() }}" alt="Theme preview : {{ $theme->getTitle() }}"/>
							{else}
								<span class="icon icon-picture-o icon-5x"></span>
							{/if}
						</div>
						{if($name === $selectedTheme->getName())}
							<i class="text-success icon icon-check-circle theme-selected icon-2x"></i>
						{/if}
						<div class="actions">
							{if($name != $selectedTheme->getName())}
								{button icon="check" class="select-theme btn-success" data-theme="{$name}" title="{text key='admin.theme-select-theme'}"}
							{/if}
							{if($theme->isRemovable())}
								{button icon="trash" class="delete-theme btn-danger" data-theme="{$name}" title="{text key='admin.theme-delete-theme'}"}
							{/if}
							{if(!empty($updates[$name]))}
								{button icon="refresh" class="update-theme btn-warning" data-theme="{$name}" title="{text key='admin.theme-update-theme'}"}
							{/if}
						</div>
					</div>

				</div>
			{/foreach}
		</div>
	</div>
</div>