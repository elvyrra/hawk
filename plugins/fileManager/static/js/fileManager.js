$(function () {
    $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
    

    $('.tree li.parent_li > span').on('click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        
        if (children.is(":visible")) {
            children.hide('fast');
            $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-folder').removeClass('icon-folder-open');
        } 
        else {
            children.show('fast');
            $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-folder-open').removeClass('icon-folder');
        }

        e.stopPropagation();
    });
    /*
    $("ol.main-tree").sortable({
        
        onDragStart2: function($item, container, _super) {
            //var attr = $item.attr('data-id');
            //alert("on drag" + attr);
            //alert("path=" + $item.find("data-path") + "or=" + $item.attr("path"));
            //$item.find('ol.dropdown-menu').sortable('disable');
            //_super($item, container);  header('X-Frame-Options: GOFORIT'); 
        },
        
        onDrop: function($item, container, _super) {
            //$item.find('ol.dropdown-menu').sortable('enable');
            //_super($item, container);
        },

        onDragStart: function(event, ui){
            //var attr = $(ui.item).attr('data-id');
            //var attr = $(ui.item).data('data-id');
            //debugger;
            //alert(attr);
        }
    });
    */
});

$('a.media').media({width:'100%', height:900});

$(".preview-file").click(function(){
    $('.media').media({ 
        width:     '100%', 
        height:    900, 
        autoplay:  true, 
        src:       $(this).data("path")
    });
});

$(".edit-folder").click(function(){
    app.dialog(app.getUri('fileManager-editFolder') + '?path=' + $(this).data("path") + '&folder=' + $(this).data("folder"));
});

$(".edit-file").click(function(){
    app.dialog(app.getUri('fileManager-editFile') + '?path=' + $(this).data("path") + '&file=' + $(this).data("file"));
});
