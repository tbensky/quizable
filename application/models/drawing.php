<?php

class Drawing extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

	function html($question_hash)
	{
		$base_url = trim(base_url(),"/");
		$base_url .= "/wpaint";
		$attach_code = "";
		$q = $this->db->query("select * from attach where question_hash=" . $this->db->escape($question_hash));
		if ($q->num_rows() == 1)
			{
				$row = $q->row_array();
				$url = site_url("welcome/view_attachment/" . $row['attach_hash'] . $row['file_ext']);
				//$attach_code = "$('#wPaint').wPaint('image', '$url');";
				$raw = $this->Question->get_raw_question_data($question_hash);
				if ($raw['type'] == 'dr' && $raw['attach_bg'] == 'yes')
					$attach_code = "image: '$url'";
			}
			
		$ret = <<<EOT
	 
      <script type="text/javascript" src="$base_url/lib/jquery.ui.widget.1.10.3.min.js"></script>
      <script type="text/javascript" src="$base_url/lib/jquery.ui.mouse.1.10.3.min.js"></script>
      <script type="text/javascript" src="$base_url/lib/jquery.ui.draggable.1.10.3.min.js"></script>
      
      <!-- wColorPicker -->
      <link rel="Stylesheet" type="text/css" href="$base_url/lib/wColorPicker.min.css" />
      <script type="text/javascript" src="$base_url/lib/wColorPicker.min.js"></script>

      <!-- wPaint -->
      <link rel="Stylesheet" type="text/css" href="$base_url/wPaint.min.css" />
      <script type="text/javascript" src="$base_url/wPaint.min.js"></script>
      <script type="text/javascript" src="$base_url/plugins/main/wPaint.menu.main.min.js"></script>
      <script type="text/javascript" src="$base_url/plugins/text/wPaint.menu.text.min.js"></script>
      <script type="text/javascript" src="$base_url/plugins/shapes/wPaint.menu.main.shapes.min.js"></script>
      

      <div id="wPaint" style="position:relative; width:800px; height:600px; background-color:#000000; margin:60px auto;"></div>
      
      <script> 
	$('#wPaint').wPaint({
          menuOffsetLeft: 0,
          menuOffsetTop: -50,
          loadImgBg: loadImgBg,
          loadImgFg: loadImgFg,
          $attach_code
        });
        

        //$attach_code;
        
         function loadImgBg () {
          this._showFileModal('bg', images);
        }

        function loadImgFg () {
          this._showFileModal('fg', images);
        }
        
        
        function grab_drawing()
        {
        	var imageData = $("#wPaint").wPaint("image");
        	$('#answer').val(imageData);
        }
        
</script>
EOT;
	echo $ret;
	}
}    
    
?>