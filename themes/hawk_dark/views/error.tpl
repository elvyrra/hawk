<div class="alert alert-{{ $level }}">
	<div class="error-title">
		<i class="fa fa-{{ $icon }}"></i>
		{{ $title }}
	</div>
	<div class="error-content">
		<pre>
{{ $message }}

{foreach($trace as $i => $line)}
	#{{ $i }} {{ $line['file'] }}:{{ $line['line'] }}

{/foreach}
</pre>
	</div>
</div>