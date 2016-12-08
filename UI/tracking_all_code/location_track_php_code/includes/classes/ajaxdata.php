<?php

class ajaxdata {

	static public function handle($map_id, $tags, $upto_id) {
		global $db, $tbl, $cfg;

		$ajaxdata = array();
		$ajaxdata['tags'] = array();

		//Go through each tag
		foreach ($tags as $tag_id) {

			$ajaxdata['tags'][$tag_id] = array();

			$cond = array();
			$cond[] = "tag_id = {$tag_id}";
			$cond[] = "map_id = {$map_id}";

			//If have an upto id
			if ($upto_id !== false) {
				$limit = '';
				$sort_dir = 'ASC';
				$cond[] = "id > {$upto_id}";
			} else {
				//Otherwise retrieve last few positions for the tag
				$limit = $cfg['livemap_load_pos'];
				$sort_dir = 'DESC';
			}

			$positions = array();

			//Retrieve its positions
			$position_result = $db->table_query($db->tbl($tbl['position']), $db->col(array('id', 'xpos', 'ypos')), $db->cond($cond, 'AND'), $db->order(array(array('id', $sort_dir))), 0, $limit);
			while ($position_record = $db->record_fetch($position_result)) {
				$positions[] = $position_record;
			}

			//If received the last few positions only, reverse array to get it back the right way
			if ($upto_id === false) {
				sort($positions);
			}

			$ajaxdata['tags'][$tag_id]['positions'] = $positions;

		}

		$ajaxdata_js = appgeneral::array_jsarray($ajaxdata);

		return $ajaxdata_js;

	}

}

?>