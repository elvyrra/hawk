 <script type="text/javascript">
    (function() {
        function initForm(app, Form) {
            var form = new Form('{{ $form->id }}', {{ $inputs }});

            {if(!empty($form->onsuccess))}
            form.onsuccess = function(data){
                {{ $form->onsuccess }}
            };
            {/if}

            {if(in_array($form->status, array($form::STATUS_ERROR, $form::STATUS_CHECK_ERROR)))}
            form.displayErrors({{ $errors }});
            {/if}

            app.forms['{{ $form->id }}'] = form;
        }

        if(require.defined('app') && require.defined('form')) {
            app = require('app');
            Form = require('form');

            initForm(app, Form);
        }
        else {
            require(['app', 'form'], (app, Form) => {
                initForm(app, Form);
            });
        }

    })();
</script>