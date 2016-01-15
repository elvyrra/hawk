{assign name="formContent"}
<div class="modal-dialog" style="{{ !empty($width) ? "width:$width;" : ""}}{{!empty($height) ? "height:$height;" : ""}}">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
			<h4 class="modal-title"><i class="icon icon-{{ !empty($icon) ? $icon : '' }}"></i> {{$title}} </h4>
		</div>

		<div class="modal-body" id="main-form">
			<div class="form-result-message">
			</div>

			<div class="row">
				{{ $form->inputs['typeAction'] }}

	        	<div ko-visible="editFolder.type() == 'removeFolder'">
				  	{{ $form->inputs['alert-remove'] }}
				</div>
	        
	       		<div ko-visible="editFolder.type() == 'editNameFolder'">
				  	{{ $form->inputs['old-name'] }}
				</div>
	        
	        	<div ko-visible="editFolder.type() == 'addFolder' || editFolder.type() == 'editNameFolder' || editFolder.type() == 'importFile' ">
				  	{{ $form->inputs['new-name'] }}
				</div>
	        
				<div ko-visible="editFolder.type() == 'addFile'">
				  	{{ $form->inputs['new-name-file'] }}
				</div>

	        	<div ko-visible="editFolder.type() == 'importFile'">
	          		{{ $form->inputs['file'] }}
	        	</div>
	        </div>
		</div>
      	
		<div class="row">
				<div class="text-center">
      				{{ $form->fieldsets['_submits'] }}
      			</div>
		</div>
	</div>
</div>

{/assign}

{form id="{$form->id}" content="{$formContent}"}
			