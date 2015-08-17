<div class="container install-container">
    <div class="col-md-8 col-md-offset-2">
        {panel type="info" title="{Lang::get('install.set-language-title')}" content="{$form}"}
    </div>
</div>
<style>
	@import url({{ Plugin::get('install')->getCssUrl() . 'install.css' }});
</style>
<script type="text/javascript" src="{{ Plugin::get('install')->getJsUrl() . 'install.js' }}"></script>