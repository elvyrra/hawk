/*global app, $, Lang */

'use strict';

require(['app'], function() {
    /**
     * Edit a language
     * */
    $('.edit-lang').click(function() {
        var tag = $('#language-filter-form [name="tag"]').val();

        app.dialog(app.getUri('edit-language', {tag : tag}));
    });

    /**
     * Delete a language
     */
    $('.delete-lang').click(function() {
        if (confirm(Lang.get('language.confirm-delete-lang'))) {
            var tag = $('#language-filter-form [name="tag"]').val();

            $.get(app.getUri('delete-language', {tag : tag}), function() {
                app.load(app.getUri('manage-languages'));
            });
        }
    });

    /**
     * Edit a translation key
     */
    $('#language-manage-page')

    /**
     * Delete a translation key
     */
    .on('click', '.delete-translation', function() {
        var tag = $('#language-filter-form [name="tag"]').val(),
            data = $(this).data('key').split('.'),
            plugin = data[0],
            key = data[1];

        $.get(app.getUri('delete-translation', {plugin : plugin, key : key, tag : tag}), function() {
            app.lists['language-key-list'].refresh();
        });
    });



    var form = app.forms['language-filter-form'];

    form.submit = function() {
        var data = this.toString();

        $.cookie('languages-filters', data);
        app.load(app.getUri('manage-languages'));
        return false;
    };

    form.node
    .on('change', '[name="tag"], [name="keys"], [name="selected"]', function() {
        form.submit();
    });
});