{assign name="formContent"}
	{{ $form->fieldsets['_submits'] }}

	<div role="tabpanel" id="settings-form-tabs" >	
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#settings-form-tab-main" role="tab" data-toggle="tab">{text key="admin.settings-main-legend"}</a></li>
			<li role="presentation"><a href="#settings-form-tab-referencing" role="tab" data-toggle="tab">{text key="admin.settings-referencing-legend"}</a></li>
			<li role="presentation"><a href="#settings-form-tab-home" role="tab" data-toggle="tab">{text key="admin.settings-home-legend"}</a></li>
			<li role="presentation"><a href="#settings-form-tab-users" role="tab" data-toggle="tab">{text key="admin.settings-users-legend"}</a></li>
			<li role="presentation"><a href="#settings-form-tab-email" role="tab" data-toggle="tab">{text key="admin.settings-email-legend"}</a></li>		
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane" id="settings-form-tab-main">
				{{ $form->fieldsets['main'] }}
			</div>

			<div role="tabpanel" class="tab-pane" id="settings-form-tab-referencing">
				<table class="table table-striped">
					<tr>
						<th></th>
						{foreach($languages as $tag => $language)}
							<th>{{ $language}} ({{ $tag }})</th>
						{/foreach}
					</tr>
					{foreach(array('title', 'description', 'keywords') as $key)}
						<tr>
							<td>{text key="{'admin.settings-' . $key . '-label'}"}</td>
							{foreach($languages as $tag => $language)}
								<th>{{ $form->fields["main_page-$key-$tag"] }}</th>
							{/foreach}
						</tr>
					{/foreach}
				</table>
			</div>
			
			<div role="tabpanel" class="tab-pane" id="settings-form-tab-home">
			{{ $form->fields['main_home-page-type'] }}

			<div data-bind="visible: homePage.type() == 'custom'">
				{{ $form->fields['main_home-page-html'] }}
			</div>

			<div data-bind="visible: homePage.type() == 'page'">
				{{ $form->fields['main_home-page-item'] }}
			</div>

			{{ $form->fields['main_open-last-tabs'] }}
			</div>
			
			<div role="tabpanel" class="tab-pane" id="settings-form-tab-users">
				{{ $form->fields['main_allow-guest'] }}		
				{{ $form->fields['roles_default-role'] }}

				{{ $form->fields['main.open-register'] }}	
				<div data-bind="visible: parseInt(register.open())">
					<div class="clearfix"></div>	
					<h3>{text key="admin.settings-register-options"}</h3>
					{{ $form->fields['main_confirm-register-email'] }}	
					<div data-bind="visible: register.checkEmail">	
						{{ $form->fields['main_confirm-email-content'] }}	
					</div>
					
					<div class="clearfix"></div>
					<h3>{text key="admin.settings-terms-options"}</h3>
					{{ $form->fields['main_confirm-register-terms'] }}		
					<div data-bind="visible: register.checkTerms">	
						{{ $form->fields['main_terms'] }}	
					</div>
				</div>
			</div>
			
			<div role="tabpanel" class="tab-pane" id="settings-form-tab-email">
				{{ $form->fields['main_mailer-from'] }}
				{{ $form->fields['main_mailer-from-name'] }}
				{{ $form->fields['main_mailer-type'] }}

				<div data-bind="visible: mail.type() == 'smtp' || mail.type() == 'pop3'">
					{{ $form->fields['main_mailer-host'] }}
					{{ $form->fields['main_mailer-port'] }}
					{{ $form->fields['main_mailer-username'] }}
					{{ $form->fields['main_mailer-password'] }}				
				</div>
				<div data-bind="visible: mail.type() == 'smtp'">
					{{ $form->fields['main_smtp-secured'] }}
				</div>
			</div>
		</div>			  
	</div>
{/assign}

{form id="{$form->id}" content="{$formContent}"}