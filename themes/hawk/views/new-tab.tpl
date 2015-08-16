<input type="hidden" class="page-name" value="{{ htmlentities(Lang::get('main.new-tab-page-name'), ENT_QUOTES) }}" />

{if($custom)}
    {{ $custom }}
{else}
    <!-- Dear developers, if you want to custom the default "new tab" content for your theme, That's here !! -->
{/if}