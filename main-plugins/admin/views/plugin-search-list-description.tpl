<p>
    {{ $description }}

    {if($price)}
        <span class="pull-right btn-info">{{ $price }}</span>
    {else}
        <span class="pull-right btn-success">{text key="admin.search-plugin-result-free"}</span>
    {/if}
</p>

<span class="meta-data">
    {text key="admin.plugin-list-description-meta-data" version="$version" author="$author"}
</span>
