<?php
$gTitle = LG_PN_LT_IMPORT;
include tpl('header');
Load::js('uploadify');
?>
<form method="post" class="form-horizontal" id="importPnLtForm" action="<?=url('basicData/importPnLt/')?>">
	<div class="control-group">
		<label class="control-label" for="file"><strong><?=LG_PURCHASE_FILE?></strong></label>
		<div class="controls">
			<input id="file" name="file" type="file" />
			<a href="<?=rurl('static/SMP-Price-File.xlsx')?>" target="_blank">Sample File</a>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<input id="submitBtn" type="submit" value="<?=LG_BTN_SAVE?>" class="btn btn-primary">
			<a class="btn" href="javascript:history.back();"><?=LG_BTN_CANCEL?></a>
		</div>
	</div>
</form>
<script type="text/javascript">
$(document).ready(function(){
	$('#file').uploadify({
		'width': '80',
		'swf'      : '<?=rurl('img/uploadify/uploadify.swf')?>',
		'uploader' : '<?=url('basicData/importPnLt')?>',
		'formData': {"id": "<?=$lt['id']?>"},
		'auto' : false,
		'buttonText': 'Browse',
		'onUploadSuccess': function(file, data, response){
			ajaxHandler(data);
		}
	});
	
	$("#importPnLtForm").submit(function(){
		$('#file').uploadify("upload", '*');
		return false;
	});
});
</script>