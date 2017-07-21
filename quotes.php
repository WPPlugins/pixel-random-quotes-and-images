<?php
function pixel_random_quote(){
	global $pixelquotes;
	$max = (count($pixelquotes) - 1);
	$random = rand(0,$max);
	
	//it is an image
	if ( substr($pixelquotes[$random][0], -5) == '.jpeg' || substr($pixelquotes[$random][0], -4) == '.png' ||
		substr($pixelquotes[$random][0], -4) == '.jpg' || substr($pixelquotes[$random][0], -4) == '.gif'){
		$title;
		
		//exist author
		if (strlen($pixelquotes[$random][2]) > 0){
			$title = "<div class=\"pix_quote_img_title\">
				<div class=\"pix_left_margin\">".$pixelquotes[$random][2]."</div>
			</div>";
		}
		$widget_content = "<div class=\"pix_quote_img_container\">
								".$title."
								<a href=\"".$pixelquotes[$random][1]."\">
									<img src=\"".$pixelquotes[$random][0]."\" >
								</a>
							</div>";
	}
	//it is a quote
	else{
		$quote_mark = '"';
		$widget_content = '<p class="pix_quote_text_container"><a href="'.$pixelquotes[$random][1].'">'.$quote_mark.$pixelquotes[$random][0].$quote_mark.'</a></p>';
		//exist author
		if (strlen($pixelquotes[$random][2]) > 0){
			$widget_content .= '<p class="pix_quote_text_author"> - '.$pixelquotes[$random][2].'</p>';
		}
	}
	return $widget_content;
}
?>