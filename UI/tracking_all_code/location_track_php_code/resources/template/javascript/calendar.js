/*
//http://www.webreference.com/programming/javascript/diaries/15/3.html
//http://www.zend.com/zend/trick/tricks-Oct-2002.php
//http://www.webreference.com/programming/javascript/diaries/15/2.html
//http://www.w3schools.com/js/js_obj_date.asp
//http://www.webreference.com/programming/javascript/diaries/15/4.html
*/

//Calendar class
function calendar(obj_ele_id, div_ele_id) {

	this.obj_ele_id = obj_ele_id;
	this.div_ele_id = div_ele_id;

	this.month_names_long = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	this.month_names_short = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
	this.day_names_long = new Array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
	this.day_names_short = new Array("S", "M", "T", "W", "T", "F", "S");
	this.month_days_total = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	this.day_start = "Mon";

	this.day_start_index_id = 0;

	this.datenow = new Date();

	this.calshow_year = this.datenow.getFullYear();
	this.calshow_month = this.datenow.getMonth() + 1;
	//this.calshow_day = this.datenow.getDate();
	this.calshow_day = undefined;

	this.field_yyyy = "";
	this.field_mm = "";
	this.field_dd = "";

	//Set calendar default date
	this.setdate = function(date_yyyy, date_mm, date_dd) {

		//If 00 convert to 2000
		date_yyyy = (date_yyyy == "00") ? 2000 : date_yyyy;

		date_yyyy = parseInt(date_yyyy, 10);
		date_mm = parseInt(date_mm, 10);
		date_dd = parseInt(date_dd, 10);

		//If 2 digit year, convert to 4 digit year
		if (date_yyyy < 99) {

			if (date_yyyy >= 70) {
				date_yyyy += 1900;
			} else {
				date_yyyy += 2000;
			}

		}

		//If year looks valid
		if ( (date_yyyy >= 1900) && (date_yyyy <= 2090) ) {

			//If month looks valid
			if ( (date_mm >= 1) && (date_mm <= 12) ) {

				this.calshow_year = date_yyyy;
				this.calshow_month = date_mm;
				this.calshow_day = date_dd;

				//alert("yy:" + date_yyyy + " " + "mm:" + date_mm + " dd:" + date_dd);

			}

		}

	}

	//Set calendar associated inpuit fields
	this.setfield = function(field_yyyy, field_mm, field_dd) {
		this.field_yyyy = field_yyyy;
		this.field_mm = field_mm;
		this.field_dd = field_dd;
	}

	//Work out the index id of the curent day
	this.workoutindexid = function(countid) {

		var day_curr_index_id = countid + this.day_start_index_id;

		if (day_curr_index_id >= 7) {
			day_curr_index_id -= 7;
		}

		return day_curr_index_id;
	}

	//Generate calendar html
	this.generate = function() {

		var calendar_html = "";

		calendar_html += "<table class=\"calendar\">\n";

		calendar_html += "\t<tr>\n";

		//Retrieve day index id
		for (i in this.day_names_long) {
			if (this.day_names_long[i] == this.day_start) {
				this.day_start_index_id  = parseInt(i, 10);
			}
		}

		//Perpare month dropdown
		var month_dropdown_html = "";
		for (i in this.month_names_long) {

			var monthindexid = parseInt(i, 10);

			var selected = "";
			if ((monthindexid+1) == this.calshow_month) {
				selected = " selected=\"selected\"";
			}

			month_dropdown_html += "<option value=\"" + (monthindexid + 1) + "\"" + selected + ">" + this.month_names_long[i] + "</option>";
		}

		month_dropdown_html = "<select size=\"1\" name=\"" + obj_ele_id + "_monthselect" + "\" id=\"" + obj_ele_id + "_monthselect" + "\" onchange=\"" + obj_ele_id + ".setdate(" + obj_ele_id + ".calshow_year, this.value, undefined); " + obj_ele_id + ".generate();\">" + month_dropdown_html + "</select>";

		//Prepare year dropdown
		var year_dropdown_html = "";
		var year_start = this.calshow_year - 10;
		var year_end = this.calshow_year + 10;
		for (i=year_start; i <= year_end; i++) {

			var selected = "";
			if (i == this.calshow_year) {
				selected = " selected=\"selected\"";
			}

			year_dropdown_html += "<option value=\"" + i + "\"" + selected + ">" + i + "</option>";
		}

		year_dropdown_html = "<select size=\"1\" name=\"" + obj_ele_id + "_monthselect" + "\" id=\"" + obj_ele_id + "_yearselect" + "\" onchange=\"" + obj_ele_id + ".setdate(this.value, " + obj_ele_id + ".calshow_month, undefined); " + obj_ele_id + ".generate();\">" + year_dropdown_html + "</select>";

		//Add on month / year title
		calendar_html += "\t<tr>\n";
		calendar_html += "\t\t<td colspan=\"7\" class=\"monthyearselection\"><div style=\"float: left\">" + month_dropdown_html + "</div><div style=\"float: right\">" + year_dropdown_html + "</div></td>\n";
		calendar_html += "\t</tr>\n";

		//Add on day titles
		for (i=0; i < 7; i++) {
			var day_curr_index_id = this.workoutindexid(i);
			calendar_html += "\t\t<td class=\"dayname\">" + this.day_names_short[day_curr_index_id] + "</td>\n";
		}

		//Get day that current month starts on
		var caldate = new Date();
		caldate.setFullYear(this.calshow_year, this.calshow_month - 1, 1);
		var month_startsonday_index_id = caldate.getDay();

		//Get total days in current month
		var month_total_days = this.get_month_days_total(this.calshow_year, this.calshow_month);

		//alert("m: " + this.calshow_month + " totday: " + month_total_days);

		var dayno = 1;

		var month_started = false;

		//Add on week row
		for (i=0; i < 7; i++) {

			calendar_html += "\t<tr>\n";

			//Add on day in week
			for (i2=0; i2 < 7; i2++) {

				var day_curr_index_id = this.workoutindexid(i2);

				var day_html = "&nbsp;";

				var dayaddinclass = "";

				//Start printing day numbers on the correct day name
				if ( (month_startsonday_index_id == day_curr_index_id) || (month_started == true) ) {

					//Stop printing days when they have all been printed for this month allready
					if (dayno <= month_total_days) {

						month_started = true;

						day_html = "<a href=\"#\" onclick=\"" + obj_ele_id + ".setdate(" + this.calshow_year + ", " + this.calshow_month + ", " + dayno + "); " + obj_ele_id + ".updatefields(); " + obj_ele_id + ".showhide(); return false;\">" + dayno + "</a>";

						if ( (this.datenow.getFullYear() == this.calshow_year) && (this.datenow.getMonth() + 1 == this.calshow_month) && (this.datenow.getDate() == dayno) ){
							dayaddinclass += " currentday";
						}

						if (this.calshow_day == dayno) {
							dayaddinclass += " dayselected";
						}

						dayno++;

					}

				}

				calendar_html += "\t\t<td class=\"day" + dayaddinclass + "\">" + day_html + "</td>\n";

			}

			calendar_html += "\t</tr>\n";

			//Break out of months loop when printed all days / blank days to fill in the table
			if (dayno > month_total_days) {
				break;
			}

		}

		var month_next = this.calshow_month + 1;
		var year_next = this.calshow_year;

		var month_prev = this.calshow_month - 1;
		var year_prev = this.calshow_year;

		//Work out next month details
		if ((this.calshow_month + 1) > 12) {
			month_next = 1;
			year_next = this.calshow_year + 1;
		} else if ((this.calshow_month - 1) < 1) {
			month_prev = 12;
			year_prev = this.calshow_year - 1;
		}

		//Add on previous / next month navigation
		calendar_html += "\t<tr>\n";
		calendar_html += "\t\t<td colspan=\"7\" class=\"monthprevnextselection\"><div style=\"float: left\"><a href=\"#\" onclick=\"" + obj_ele_id + ".setdate(" + year_prev + ", " + month_prev + ", undefined); " + obj_ele_id + ".generate(); return false;\">&laquo; " + this.month_names_long[month_prev-1] + "</a></div><div style=\"float: right\"><a href=\"#\" onclick=\"" + obj_ele_id + ".setdate(" + year_next + ", " + month_next + ", undefined); " + obj_ele_id + ".generate(); return false;\">" + this.month_names_long[month_next-1] + " &raquo;</a></div></td>\n";
		calendar_html += "\t</tr>\n";

		calendar_html += "</table>\n";

		document.getElementById(this.div_ele_id).innerHTML = calendar_html;

	}

	//Get days in set month
	this.get_month_days_total = function(year, month) {
		year = parseInt(year, 10);
		month = parseInt(month, 10);
		var month_days_total = this.month_days_total.slice();

		if (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) {
			month_days_total[1] = "29";
		}

		//alert("y: " +  year + " totday: " + month_days_total[month-1]);

		return month_days_total[month-1];
	}

	//Show hide calendar
	this.showhide = function() {
		var display = document.getElementById(this.div_ele_id).style.display;
		if (display == "none") {

			//If date fields are entered, default calendar to date
			if ( (document.getElementById(this.field_yyyy)) && (document.getElementById(this.field_mm)) && (document.getElementById(this.field_dd)) ) {

				if ( (document.getElementById(this.field_yyyy).value) && (document.getElementById(this.field_mm).value) && (document.getElementById(this.field_dd).value) ) {
					this.setdate(document.getElementById(this.field_yyyy).value, document.getElementById(this.field_mm).value, document.getElementById(this.field_dd).value);
				} else if ( (document.getElementById(this.field_yyyy).value) && (document.getElementById(this.field_mm).value) ) {
					this.setdate(document.getElementById(this.field_yyyy).value, document.getElementById(this.field_mm).value, undefined);
				} else if (document.getElementById(this.field_yyyy).value) {
					this.setdate(document.getElementById(this.field_yyyy).value, 1, undefined);
				}

			}

			this.generate();
			document.getElementById(this.div_ele_id).style.display = "";
		} else {
			document.getElementById(this.div_ele_id).style.display = "none";
		}
	}
	
	//Update date fields
	this.updatefields = function() {

		if (this.field_yyyy != "") {
			document.getElementById(this.field_yyyy).value = this.calshow_year;
		}

		if (this.field_mm != "") {
			document.getElementById(this.field_mm).value = this.calshow_month;
		}

		if (this.field_dd != "") {
			document.getElementById(this.field_dd).value = this.calshow_day;
		}

	}

}