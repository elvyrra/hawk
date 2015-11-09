$(".update-hawk").click(function(){
    if(confirm(Lang.get('admin.update-page-confirm-update-hawk'))){
        $.get(app.getUri('update-hawk', {version : $(this).data('to') }))

        .success(function(response){
            if(response.status){
                location.reload();
            }
            else{
                app.notify('error', response.message);
            }
        })

        .error(function(xhr, code, error){
            app.notify('error', error);
        });
    }
});