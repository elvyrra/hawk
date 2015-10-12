{if($updates)}
    <div class="available-updates alert-info navbar-right pointer" title="{{ $title }}" href="{uri action='updates-index'}" target="newtab">
        <i class="icon icon-exclamation"></i>
        {{ $updates }}
    </div>
{/if}