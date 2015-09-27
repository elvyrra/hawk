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
								<th>{{ $form->fields["main.page-$key-$tag"] }}</th>
							{/foreach}
						</tr>
					{/foreach}
				</table>
			</div>
			
			<div role="tabpanel" class="tab-pane" id="settings-form-tab-home">
			{{ $form->fields['main.home-page-type'] }}

			<div data-bind="visible: homePage.type() == 'custom'">
				{{ $form->fields['main.home-page-html'] }}
			</div>

			<div data-bind="visible: homePage.type() == 'page'">
				{{ $form->fields['main.home-page-item'] }}
			</div>

			{{ $form->fields['main.open-last-tabs'] }}
			</div>
			
			<div role="tabpanel" class="tab-pane" id="settings-form-tab-users">
				{{ $form->fields['main.allow-guest'] }}		
				{{ $form->fields['roles.default-role'] }}

				{{ $form->fields['main.open-register'] }}	
				<div data-bind="visible: parseInt(register.open())">
					<div class="clearfix"></div>	
					<h3>{text key="admin.settings-register-options"}</h3>
					{{ $form->fields['main.confirm-register-email'] }}	
					<div data-bind="visible: register.checkEmail">	
						{{ $form->fields['main.confirm-email-content'] }}	
					</div>
					
					<div class="clearfix"></div>
					<h3>{text key="admin.settings-terms-options"}</h3>
					{{ $form->fields['main.confirm-register-terms'] }}		
					<div data-bind="visible: register.checkTerms">	
						{{ $form->fields['main.terms'] }}	
					</div>
				</div>
			</div>
			
			<div role="tabpanel" class="tab-pane" id="settings-form-tab-email">
				{{ $form->fields['main.mailer-from'] }}
				{{ $form->fields['main.mailer-from-name'] }}
				{{ $form->fields['main.mailer-type'] }}

				<div data-bind="visible: mail.type() == 'smtp' || mail.type() == 'pop3'">
					{{ $form->fields['main.mailer-host'] }}
					{{ $form->fields['main.mailer-port'] }}
					{{ $form->fields['main.mailer-username'] }}
					{{ $form->fields['main.mailer-password'] }}				
				</div>
				<div data-bind="visible: mail.type() == 'smtp'">
					{{ $form->fields['main.smtp-secured'] }}
				</div>
			</div>
		</div>			  
	</div>
{/assign}

{form id="{$form->id}" content="{$formContent}"}