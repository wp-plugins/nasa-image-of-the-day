<?php
/*
Plugin Name: NASA Image Of The Day
Description: Adds a sidebar widget to display the NASA Image of the Day (NASA IOTD)
Version:     1.0
Author:      Olav Kolbu
Author URI:  http://www.kolbu.com/
Plugin URI:  http://wordpress.org/extend/plugins/nasa-image-of-the-day/

Based on code from various other GPL plugins.

*/
/*
Copyright (C) 2008 kolbu.com (olav AT kolbu DOT com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function widget_niotdwidget_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;
		
	function widget_niotdwidget_returnimageandlink()
	{
		$niotdAvailable = true;
		
		$options = get_option('widget_niotdwidget');

		// Set the image width to 150 pixels
		$ImageWidthConst = 195;
		// Set the base URL for the NIOTD site, thanks NASA!
		$URL = 'http://www.nasa.gov';
		$FullURL = $URL.'/home/index.html';
		
		// Create the regexs needed
		$RegExLead = 'Image of the Day Gallery';
		$RegExImage = '^<a href="([^"]+)"><IMG WIDTH="[^"]+" ALT="[^"]+" TITLE="[^"]+" SRC="([^"]+)".*$';
		$RegExTitle = '^<h3[^>]+>([^<]+)';
		$RegExText = '^([^<]+)</p>';
		
		// Regular expression to validate that we have a good image to present
		$RegExImageExtension= '.*(\.[Jj][Pp][Gg]|\.[Gg][Ii][Ff]|\.[Jj][Pp][Ee][Gg]|\.[Pp][Nn][Gg])';

		// Open the remote file using the full URL, read-only
		if($RemoteFile = fopen($FullURL, "r")) {
			if ($RemoteFile) {
				// Loop until we're at the end of the file
				while (!feof($RemoteFile)) {
				   $buffer = fgets($RemoteFile, 1024);
					// look for lead in
					if (eregi ($RegExLead, $buffer, $out)) {
					    $LeadFound = true;
					}
					if ( ! $LeadFound ) {
					    continue;
					}
					if (!$NIOTDTitle && eregi ($RegExTitle, $buffer, $out))
						$NIOTDTitle = $out[1];
					if (!$NIOTDUrl && eregi ($RegExImage, $buffer, $out)) {
						$NIOTDUrl = $out[1];
						$NIOTDImage = $out[2];
					}
					// Check for the text
					if (!$NIOTDText && eregi ($RegExText, $buffer, $out)) {
						$NIOTDText = $out[1];
						break;
					}
			   }
			   // All done, we're closed, get out!
			   fclose($RemoteFile);
			}
		}
		// We didn't find the NIOTD page, set available bool to false
		else {
			$niotdAvailable = false;
		}
		// If the NIOTD is available, perform our mojo
		if ($niotdAvailable)
		{
			// Image url
			$ImageUrl = $URL.$NIOTDImage;
			
			// Check to make sure the image is a compatible format
			if(eregi($RegExImageExtension, $ImageUrl)) {
						
				// Get the dimensions of the image for resizing
				$ImageDimensionsSmall = @getimagesize($ImageUrl);
				// We want a proportional image, so create our resize percentage based on the width
				$ImageResizePercentage = ($ImageDimensionsSmall[0] / $ImageWidthConst);
				// Set the image width to our constant
				$ImageWidthSmall = ($ImageWidthConst);
				// Set the image height using the resize percentage, again porpotions are the key
				$ImageHeightSmall = @($ImageDimensionsSmall[1] / $ImageResizePercentage);

				// Create the hyperlink to the NIOTD site, wrapped around the image itself, setting the target to a new window and passing in the image height and width
				$ImageAndLink = '<b>'.$NIOTDTitle.'</b><br><a href="'.$URL.$NIOTDUrl.'" target="_blank"><img src="'.$ImageUrl.'" title="'.$NIOTDTitle.'" width="'.$ImageWidthSmall.'" height="'.$ImageHeightSmall.'"/></a><br>&nbsp;<br><font size="-1">'.$NIOTDText.' <a target="_blank" href="'.$URL.$NIOTDUrl.'"><br />Read More</a></font>';

				// El fin!  Return the results
				return $ImageAndLink;
			}
			else {
				$niotdAvailable = false;
			}
		}
		// The NIOTD wasn't available, return a message indicating as such
		if (!$niotdAvailable) {
			return '<p>NIOTD not available</p>';
		}
	}
		
	function widget_niotdwidget($args) {
		    extract($args);
			
			// Each widget can store its own options. We keep strings here.
			$options = get_option('widget_niotdwidget');
			$title = $options['title'];
			
			echo $before_widget;
		    echo $before_title . $title . $after_title;
			$NiodgImageAndLink = widget_niotdwidget_returnimageandlink();
		    echo '<div style="margin-top:5px;text-align:left;">'.
                          $NiodgImageAndLink.'</div>';
		    echo $after_widget;
	}
	
	// This function creates the widget control, using the built in widget abilities for controling widgets (they only get the change the title, it's no big deal)
	function widget_niotdwidget_control() {

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_niotdwidget');
		if ( !is_array($options) )
		if ( $_POST['niotdwidget-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['niotdwidget-title']));
			update_option('widget_niotdwidget', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		
		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		echo '<p style="text-align:right;"><label for="niotdwidget-title">Title: <input style="width: 200px;" id="niotdwidget-title" name="niotdwidget-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><em>Changes wont be reflected until after saving</em></p>';
		echo '<input type="hidden" id="niotdwidget-submit" name="niotdwidget-submit" value="1" />';
	}

	// This registers our optional widget control form. Because of this
	// our widget will have a button that reveals a 200x200 pixel form.
	register_widget_control('NASA IOTD', 'widget_niotdwidget_control', 200, 200);
	
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	// Description is only settable by not calling wrapper-function...?
	wp_register_sidebar_widget(sanitize_title('NASA IOTD'), 'NASA IOTD', 
                                   'widget_niotdwidget',
                              array('description' => __('NASA Image Of The Day')));
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_niotdwidget_init');
?>
