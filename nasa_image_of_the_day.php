<?php
/*
Plugin Name: NASA Image Of The Day
Description: Adds a sidebar widget to display the NASA Image of the Day (NASA IOTD)
Version:     2.0
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

include_once(ABSPATH . WPINC . '/rss.php');

function widget_niotdwidget_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;
		
	function widget_niotdwidget_returnimageandlink()
	{
		$options = get_option('widget_niotdwidget');

                // Set the image width to user default pixels, default 195 if 0
                $ImageWidth = $options['width'] ? $options['width'] : 195;

		// Set the base URL for the NIOTD site, thanks NASA!
		$URL = 'http://www.nasa.gov';
		$FullURL = $URL.'/rss/image_of_the_day.rss';

		define('MAGPIE_CACHE_AGE', 60);
		define('MAGPIE_CACHE_ON', 1);
		define('MAGPIE_DEBUG', 0);

		$rss = fetch_rss($FullURL);
		if ( is_object($rss) ) {
                    // Get the dimensions of the image for resizing
		    $ImageDimensions = @getimagesize($rss->image['url']);

                    // We want a proportional image, so create our resize percentage based on the width
                    $ImageResizePercentage = ($ImageDimensions[0] / $ImageWidth);

                    // Set the image height using the resize percentage, again porpotions are the key
                    $ImageHeight = @($ImageDimensions[1] / $ImageResizePercentage);


		    print '<b>'.$rss->items[0]['title'].'</b><br><a href="'.
                          $rss->items[0]['link'].'" target="_blank"><img src="'.
                          $rss->image['url'].'" title="'.$rss->items[0]['title'].
                          '" width="'.$ImageWidth.'" height="'.$ImageHeight.
                          '"/></a><br>&nbsp;<br><font size="-1">'.
                          $rss->items[0]['description'].' <a target="_blank" href="'.
                          $rss->items[0]['link'].'"><br />Read More</a></font>';
		} else {
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
		if ( $_POST['niotdwidget-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['niotdwidget-title']));
			$options['width'] = strip_tags(stripslashes($_POST['niotdwidget-width']));
			update_option('widget_niotdwidget', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$width = htmlspecialchars($options['width'], ENT_QUOTES);
		
		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		echo '<p style="text-align:right;"><label for="niotdwidget-title">Title: <input style="width: 200px;" id="niotdwidget-title" name="niotdwidget-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="niotdwidget-width">Width: <input style="width: 50px;" id="niotdwidget-width" name="niotdwidget-width" type="text" value="'.$width.'" /></label></p>';
		echo '<p style="text-align:right;">Images will be propotionally scaled to width.</p>';
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
