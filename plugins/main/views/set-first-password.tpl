<div class="container set-password-container">
    <div class="col-md-8 col-md-offset-2">
        {assign name="panelContent"}
            <h3>{text key="main.set-first-password-intro"}</h3>
            {{ $form }}
        {/assign}
        {panel type="info" title="{text key='main.set-first-pwd-form-title'}" content="{$panelContent}" icon="sign-in"}

    </div>
</div>