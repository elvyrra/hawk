{import "form-input-textarea.tpl"}

<script type="text/javascript">
	CKEDITOR.replace("{{ $input->id }}", {
		language : 'en',
		removeButtons : 'Save,Scayt,Rtl,Ltr,Language,Flash',		
	});	
</script>