var ajax_update_url;
var upto_id;
var tagpos_queue = {};
var ajax_request_timeout;
var animation_speed = 1000;

//Init 'Unobtrusive' JavaScript
//addEvent(window, "load", function() {
//});

$(function() {
	$('#svgmapcontainer').svg({onLoad: loadlivemap});
});



function readSingleFile() {
    var mydata = JSON.parse(data);
	
	var toPrint = "<div class=\"singleSoldier\"> <a class=\"item\"> Solder ID: " + mydata[0].node_id + " located in sector " + mydata[0].node_id + ", last seen 00:01:21" + "</div>";
	
	document.getElementById("soldierMenu").innerHTML = toPrint;
 }


//Load SVG
function loadlivemap(svg) {

	//Save scg into the global scope for use in other functions
	window.svg = svg;
	
	readSingleFile();
	
	//http://keith-wood.name/svg.html

	//Load background
	svg.image(0, 0, map_background_width, map_background_height, map_background);

	//Load readers
	var g = svg.group({opacity: 0});

	//Init group for path lines
	//var svg_lines = svg.group();

	//Go through all readers and add them to the map
	for (var i in reader_locations) {

		var reader = svg.circle(g, reader_locations[i]["xpos"], reader_locations[i]["ypos"], 0, {fill: '#ff1900', stroke: '#000000', 'stroke-width': 3});
		$(reader).animate({svgR: 11}, 500);

	}

	$(g).animate({opacity: 0.9}, 500);


	//Init URL for updates
	ajax_update_url = baseurl;

	//Tags group
	var svg_tags_g = svg.group();

	//Go through all tags and add them to the url, add them to the map, init the tag queue
	for (var i in tags) {

		ajax_update_url += "&tagid[]=" + tags[i]["id"];

		var tag_id = tags[i]["id"];
		tagpos_queue[tag_id] = [];

		//Add tag to the map hidden
		var tag = svg.circle(svg_tags_g, 0, 0, 0, {fill: tags[i]["colour"], stroke: '#000000', 'stroke-width': 2, 'opacity': 0});

		tags[i]["svg"] = tag;
		tags[i]["new"] = true;

		tags[i]["lastpos"] = [];

	}

	//alert(ajax_update_url);
	//index.php?p=livemap_ajax&map_id=9&tagid[]=2&tagid[]=3

	//Parse initial ajax data (tag positions)
	parse_ajaxdata();

	//Hadle initial tag queue
	handle_tagpos_queue();

	//Init ajax request to poll for new data at regular intervals
	ajax_request_init();

	//Set queue processing to occur
	setInterval("handle_tagpos_queue()", animation_speed);

}

//Init ajax request
function ajax_request_init() {
	ajax_request_timeout = window.setTimeout("ajax_request();", livemap_ajax_refresh_sec * 1000);
}

//ajax request handle
function ajax_request() {

	var geturl = ajax_update_url + "&t=pos&upto_id=" + upto_id + "&nocache=" + new Date().getTime();
	var loader = new net.ContentLoader(geturl, ajax_request_process);

}

//Handle ajax responce
function ajax_request_process() {

	eval("ajaxdata = " + this.req.responseText);

	//Parse ajax data
	parse_ajaxdata();

	//Hadle tag queue
	//handle_tagpos_queue();

	//Trigger another request to happen shortly
	ajax_request_init();

}

//Parse ajaxdata
function parse_ajaxdata() {

	//Go through all tags
	for (var i in tags) {

		var tag_id = tags[i]["id"];

		//Go through all position for tag
		for (var pos in ajaxdata["tags"][tag_id]["positions"]) {

			//Add new position to the queue
			tagpos_queue[tag_id].push(ajaxdata["tags"][tag_id]["positions"][pos]);

			//If have an upto_id greater than the previously seen value
			if ( (upto_id == undefined) || (ajaxdata["tags"][tag_id]["positions"][pos]["id"] > upto_id) ) {

				//Update the last id seen (ready for the next request)
				upto_id = ajaxdata["tags"][tag_id]["positions"][pos]["id"];

			}

		}

	}

	//alert(dump(tagpos_queue));
	//alert(upto_id);

}

//Handle tag position action queue
function handle_tagpos_queue() {

	//alert(dump(tagpos_queue));

	//Go through all tags
	for (var tag in tags) {

		var tag_id = tags[tag]["id"];

		//Go through all position actions for tag
		var processed_pos = 0;
		for (var pos in tagpos_queue[tag_id]) {

			//Work out declustered position
			var decluster_pos = cluster_tags(tagpos_queue[tag_id][pos]["xpos"], tagpos_queue[tag_id][pos]["ypos"]);

			//If tag is hidden
			if (tags[tag]["new"] == true) {

				//Set as now not new
				tags[tag]["new"] = false;

				//Move to initial position
				$(tags[tag]["svg"]).animate({svgCx: decluster_pos["xpos"], svgCy: decluster_pos["ypos"]}, 0);

				//Show in initial position
				$(tags[tag]["svg"]).animate({'opacity': 0.7, 'svgR': 6}, animation_speed);

			} else {

				//Init line from old position to new position
				//line(parent, x1, y1, x2, y2, settings)
				//var pathline = svg.line(null, tags[tag]["lastpos"][0], tags[tag]["lastpos"][1], decluster_pos["xpos"], decluster_pos["ypos"], {strokeWidth: 1, stroke: 'green'});
				var pathline = svg.line(null, tags[tag]["lastpos"]["xpos"], tags[tag]["lastpos"]["ypos"], tags[tag]["lastpos"]["xpos"], tags[tag]["lastpos"]["ypos"], {strokeWidth: 5, stroke: '#ffffff'});

				//Animate line to new position
				$(pathline).animate({ svgX2: decluster_pos["xpos"], svgY2: decluster_pos["ypos"], svgStroke: tags[tag]["colour"], svgStrokeWidth: 2}, animation_speed);

				//Animate marker to new position
				$(tags[tag]["svg"]).animate({svgCx: decluster_pos["xpos"], svgCy: decluster_pos["ypos"]}, animation_speed);

			}

			//Save position
			tags[tag]["lastpos"] = {
				"xpos": decluster_pos["xpos"],
				"ypos": decluster_pos["ypos"]
			};

			processed_pos++;

			//Remove this (first item) from the queue, it has been processed
			//tagpos_queue[tag_id].shift()

			//Processed an action, stop here
			break;

		}

		//Clear all processed positions
		if (processed_pos > 0) {

			for (var i = 0; i < processed_pos; i++) {
				tagpos_queue[tag_id].shift()
			}

		}

	}

}

//Handle clustering of tags so they do not overlap / are not placed on top of each other or readers
function cluster_tags(xpos, ypos) {

	var clust_pos_pad = 15;
	var map_edge_pad = 20;
	var max_dist_away = 70;

	//var debugdata = "";

	//svg.circle(xpos, ypos, 6, {fill: '#000000', stroke: '#000000', 'stroke-width': 2, 'opacity': 50});

	var found_position = false;

	var decluster_pos = {
		"xpos": "",
		"ypos": ""
	};

	//Offset X
	for (var pos_offset_x=clust_pos_pad; pos_offset_x < max_dist_away; pos_offset_x+=clust_pos_pad) {

		//Offset Y
		for (var pos_offset_y=clust_pos_pad; pos_offset_y < max_dist_away; pos_offset_y+=clust_pos_pad) {

			//Offset Left/Right
			for (var pos_offset_lr=0; pos_offset_lr < 2; pos_offset_lr++) {

				//Offset Top/Bottom
				for (var pos_offset_tb=0; pos_offset_tb < 2;pos_offset_tb++) {

					var position_usable = true;

					//debugdata += pos_offset_tb + "\n";

					//Work out if offset should be left/right of position
					if (pos_offset_lr == 1) { //Right

						pos_cluster_x = xpos + pos_offset_x;

						//Check not off the right side of the map (with padding)
						if (pos_cluster_x > (map_background_width - map_edge_pad)) {
							position_usable = false;
						}

					} else { //Left

						pos_cluster_x = xpos - pos_offset_x;

						//Check not off the left side of the map (with padding)
						if (pos_cluster_x < map_edge_pad) {
							position_usable = false;
						}

					}

					//Work out if offset should be top/bottom of position
					if (pos_offset_tb == 1) { //Bottom

						pos_cluster_y = ypos + pos_offset_y;

						//Check not over the max height of the map (with padding)
						if (pos_cluster_y > (map_background_height - map_edge_pad)) {
							position_usable = false;
						}

					} else { //Top

						pos_cluster_y = ypos - pos_offset_y;

						//Check not under the map (with padding)
						if (pos_cluster_y < map_edge_pad) {
							position_usable = false;
						}

					}

					//Go through all tags checking none area near the possible proposed position
					for (var tag in tags) {

						//If tag is on the map
						if (tags[tag]["lastpos"]["xpos"] != "") {

							//Check position is not close to an existing tag
							if (
									(
										(pos_cluster_x > (tags[tag]["lastpos"]["xpos"] - clust_pos_pad)) && (pos_cluster_x < (tags[tag]["lastpos"]["xpos"] + clust_pos_pad)) //x axis
									)
								&&
									(
										(pos_cluster_y > (tags[tag]["lastpos"]["ypos"] - clust_pos_pad)) && (pos_cluster_y < (tags[tag]["lastpos"]["ypos"] + clust_pos_pad)) //y axis
									)
							) {

									position_usable = false;

							}

						}

					}

					//If position has not been found already and is usable
					if ((found_position == false) && (position_usable == true) ) {

						//svg.circle(pos_cluster_x, pos_cluster_y, 6, {fill: '#ffff00', stroke: '#000000', 'stroke-width': 2, 'opacity': 50});

						found_position = true;

						decluster_pos = {
							"xpos": pos_cluster_x,
							"ypos": pos_cluster_y
						};

					} else {
						//Possible position
						//svg.circle(pos_cluster_x, pos_cluster_y, 6, {fill: 'none', stroke: '#000000', 'stroke-width': 2, 'opacity': 50});
					}

					if (found_position == true) {
						break;
					}

				}

				if (found_position == true) {
					break;
				}

			}

			if (found_position == true) {
				break;
			}

		}

		if (found_position == true) {
			break;
		}

	}

	//alert(debugdata);

	return decluster_pos;

}

/*
function drawInitial(svg) {
	svg.circle(75, 75, 50, {fill: 'none', stroke: 'red', 'stroke-width': 3});
	var g = svg.group({stroke: 'black', 'stroke-width': 2});
	svg.line(g, 15, 75, 135, 75);
	svg.line(g, 75, 15, 75, 135);

var myrect = svg.rect(25, 25, 150, '25%', 10, 10, 
    {fill: 'none', stroke: 'blue', strokeWidth: 3, 
    transform: 'rotate(0, 100, 75)'}); 

$(myrect).animate({svgWidth: 200, svgHeight: '30%', 
    svgStroke: 'aqua', svgStrokeWidth: '+=7', 
    svgTransform: 'rotate(60, 100, 75)'}, 2000);

$(myrect).animate({svgX: 200}, 2000);

var g = svg.group({stroke: 'green'}); 
var bb = svg.line(g, 450, 120, 550, 20, {strokeWidth: 5}); 

svg.polyline([[450,250], 
    [475,250],[475,220],[500,220],[500,250], 
[525,250],[525,200],[550,200],[550,250], 
[575,250],[575,180],[600,180],[600,250], 
[625,250],[625,160],[650,160],[650,250],[675,250]], 
{fill: 'none', stroke: 'blue', strokeWidth: 5}); 
svg.polygon([[800,150],[900,180],[900,240],[800,270],[700,240],[700,180]], 
{fill: 'lime', stroke: 'blue', strokeWidth: 10}); 



$(bb).animate({svgX1: 10}, 2000);

}
*/

