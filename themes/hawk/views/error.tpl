<div class="alert alert-{{ $level }}">
	<div class="error-title">
		{icon icon="{$icon}"}
		{{ $title }}
	</div>
	<div class="error-content">
		<pre>
{{ $message }}

{foreach($trace as $i => $line)}
	#{{ $i }} {{ empty($line['file']) ? '' : $line['file'] }}{{ empty($line['line']) ? '' : ':' . $line['line'] }}
{/foreach}
</pre>
	</div>
</div>