$(document).ready(function() { 
  tinyMCE.init({

	  // General options
	  mode : "textareas",
	  theme: "modern",
	  plugins: [
		  "advlist autolink lists link image charmap print preview hr anchor pagebreak",
		  "searchreplace wordcount visualblocks visualchars code fullscreen",
		  "insertdatetime media nonbreaking save table contextmenu directionality",
		  "emoticons template paste textcolor"
	  ],
	  toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
	  toolbar2: "print preview media | forecolor backcolor emoticons",
			image_advtab: true,
	  forced_root_block : "",
	  force_br_newlines : true,
	  force_p_newlines : false

  });
});