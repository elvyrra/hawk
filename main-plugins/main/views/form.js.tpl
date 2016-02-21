 <script type="text/javascript">
     (function(){
        function init(){
            var form = new Form('{{ $form->id }}', {{$inputs}});

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

        if(window.app){
            init();
        }
        else{
            require(["app"], init);
        }
    })();
</script>