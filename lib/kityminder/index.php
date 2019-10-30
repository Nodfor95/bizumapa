


    <!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>KityMinder Editor - Powered By FEX</title>


	<link rel="stylesheet" href="./bower_components/bootstrap/dist/css/bootstrap.css" />
	<link rel="stylesheet" href="./bower_components/codemirror/lib/codemirror.css" />
	<link rel="stylesheet" href="./bower_components/hotbox/hotbox.css" />
	<link rel="stylesheet" href="./bower_components/kityminder-core/dist/kityminder.core.css" />
	<link rel="stylesheet" href="./bower_components/color-picker/dist/color-picker.min.css"/>
	<link rel="stylesheet" href="./kityminder.editor.min.css">

	<style>
		div.minder-editor-container {
			position: absolute;
			top: 0px;
			bottom: 0;
			left: 0;
			right: 0;
		}
	</style>
</head>
<body ng-app="kityminderDemo" ng-controller="MainController">

<kityminder-editor on-init="initEditor(editor, minder)" data-theme="fresh-green"></kityminder-editor>
<iframe name="frameFile" style="display:none;"></iframe>



</body>


<script src="./bower_components/jquery/dist/jquery.js"></script>
<script src="./bower_components/bootstrap/dist/js/bootstrap.js"></script>
<script src="./bower_components/angular/angular.js"></script>
<script src="./bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>
<script src="./bower_components/codemirror/lib/codemirror.js"></script>
<script src="./bower_components/codemirror/mode/xml/xml.js"></script>
<script src="./bower_components/codemirror/mode/javascript/javascript.js"></script>
<script src="./bower_components/codemirror/mode/css/css.js"></script>
<script src="./bower_components/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="./bower_components/codemirror/mode/markdown/markdown.js"></script>
<script src="./bower_components/codemirror/addon/mode/overlay.js"></script>
<script src="./bower_components/codemirror/mode/gfm/gfm.js"></script>
<script src="./bower_components/angular-ui-codemirror/ui-codemirror.js"></script>
<script src="./bower_components/marked/lib/marked.js"></script>
<script src="./bower_components/kity/dist/kity.min.js"></script>
<script src="./bower_components/hotbox/hotbox.js"></script>
<script src="./bower_components/json-diff/json-diff.js"></script>
<script src="./bower_components/kityminder-core/dist/kityminder.core.min.js"></script>
<script src="./bower_components/color-picker/dist/color-picker.min.js"></script>


<script src="./kityminder.editor.js"></script>
<script src="./diy.js"></script>

<script>

	angular.module('kityminderDemo', ['kityminderEditor'])
	.controller('MainController', function($scope) {
		$scope.initEditor = function(editor, minder) {
			window.editor = editor;
			window.minder = minder;
			window.minder.execCommand('template', 'right');

		};
	});
</script>


<!--                  <?php
              echo var_dump($_GET);
          ?> -->


</html>
