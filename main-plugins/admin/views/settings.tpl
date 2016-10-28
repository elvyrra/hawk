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
								<th>{{ $form->inputs["main_page-$key-$tag"] }}</th>
							{/foreach}
						</tr>
					{/foreach}
				</table>
			</div>

			<div role="tabpanel" class="tab-pane" id="settings-form-tab-home">
			{{ $form->inputs['main_home-page-type'] }}

			<div e-show="homePage.type == 'custom'">
				{{ $form->inputs['main_home-page-html'] }}
			</div>

			<div e-show="homePage.type == 'page'">
				{{ $form->inputs['main_home-page-item'] }}
			</div>

			{{ $form->inputs['main_open-last-tabs'] }}
			</div>

			<div role="tabpanel" class="tab-pane" id="settings-form-tab-users">
				{{ $form->inputs['main_allow-guest'] }}
				{{ $form->inputs['roles_default-role'] }}

				{{ $form->inputs['main_open-register'] }}
				<div e-show="parseInt(register.open)">
					<div class="clearfix"></div>
					<h3>{text key="admin.settings-register-options"}</h3>
					{{ $form->inputs['main_confirm-register-email'] }}
					<div e-show="register.checkEmail">
						{{ $form->inputs['main_confirm-email-content'] }}
					</div>

					<div class="clearfix"></div>
					<h3>{text key="admin.settings-terms-options"}</h3>
					{{ $form->inputs['main_confirm-register-terms'] }}
					<div e-show="register.checkTerms">
						{{ $form->inputs['main_terms'] }}
					</div>
				</div>
			</div>

			<div role="tabpanel" class="tab-pane" id="settings-form-tab-email">
				{{ $form->inputs['main_mailer-from'] }}
				{{ $form->inputs['main_mailer-from-name'] }}
				{{ $form->inputs['main_mailer-type'] }}

				<div e-show="mail.type == 'smtp' || mail.type == 'pop3'">
					{{ $form->inputs['main_mailer-host'] }}
					{{ $form->inputs['main_mailer-port'] }}
					{{ $form->inputs['main_mailer-username'] }}
					{{ $form->inputs['main_mailer-password'] }}
				</div>
				<div e-show="mail.type == 'smtp'">
					{{ $form->inputs['main_smtp-secured'] }}
				</div>
			</div>
		</div>
	</div>
{/assign}

{form id="{$form->id}" content="{$formContent}"}