/**********************************************************************
 *    						date.js
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
Date.prototype.DAYS = [
	{fr : "Dimanche", en : "Sunday"},
	{fr : "Lundi", en : "Monday"},
	{fr : "Mardi", en : "Tuesday"},
	{fr : "Mercredi", en : "Wednesday"},
	{fr : "Jeudi", en : "Thursday"},
	{fr : "Vendredi", en : "Friday"},
	{fr : "Samedi", en : "Saturday"}
];

Date.prototype.MONTHES = [
	{fr : "Janvier", en : "January"},
	{fr : "Février", en : "February"},
	{fr : "Mars", en : "March"},
	{fr : "Avril", en : "April"},
	{fr : "Mai", en : "May"},
	{fr : "Juin", en : "June"},
	{fr : "Juillet", en : "July"},
	{fr : "Août", en : "August"},
	{fr : "Septembre", en : "September"},
	{fr : "Octobre", en : "October"},
	{fr : "Novembre", en : "November"},
	{fr : "Décembre", en : "December"},
];

Date.prototype.toFrenchString = function(){
	return this.format("J d f Y - H<h>i");	
};

Date.prototype.format = function(format){
	var result = "";
	var unformatted = false;
	
	for(var i = 0; i < format.length; i++){
		var c = format.charAt(i);		
		switch(c){
			/*** Start a new unformatted section ***/
			case "<" : unformatted = true; break;
			
			/*** Stop a unformatted section ***/
			case ">" : unformatted = false; break;
			
			/*** The letter formating the output ***/			
			default : 
				result += (unformatted) ? c : this.output(c);
			break;
		}		
	}
	
	return result;
};

Date.prototype.output = function(letter){	
	var monthes = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	var daysInMonthes = new Array(31, this.isLeapYear() ? 29 : 28, 31,30,31,30,31,31,30,31,30,31);
	
	switch(letter){
		/* The day of the month on 2 figures */
		case "d": return (this.getDate() < 10) ? "0"+this.getDate() : this.getDate();
		
		/* the day of the week, on 3 letters */
		case "D": return this.DAYS[this.getDay()].en.substr(0,3);
		
				/* the day of the month, without initial 0 */
		case "j": return this.getDate();		
		
		/* the whole day of the week (Sunday to Saturday) */
		case "l" : return this.DAYS[this.getDay()].en;
		
		/* The whole day of the week in French */
		case "J": return this.DAYS[this.getDay()].fr;
			
		
		/* ISO-8601 representation of the day in the week */
		case "N" : return this.getDay() + 1;
		
		/* ordinal suffix of the day of the month , on 2 letters */
		case "S": 
			var d = this.getDate();
			if(d % 10 == 1) return "st";
			if(d % 10 == 2) return "nd";
			if(d % 10 == 3) return "rd";
			return "th";
		
		/* Week day */
		case "w": return this.getDay();

		/* day of the year */
		case "z" :
			var onejan = new Date(this.getFullYear(),0,1);
			return Math.ceil((this - onejan) / 86400000);
		
		/*	Number of the week ISO-8601 */	
		// case "W":	
			// var firstDay = new Date(this.getFullYear(), 0,1).getDay();
			
		/* Month name */
		case "F":
			return this.MONTHES[this.getMonth()].en;
		
		/* Month name in French */
		case "f" :
			return this.MONTHES[this.getMonth()].fr;
		
		/* Numerical month, with initial 0*/
		case "m":
			var m = this.getMonth()+1;
			return (m < 10) ? "0"+m : m;
			
		/* Month on 3 letters */
		case "M":
			return this.MONTHES[this.getMonth()].en.substr(0,3);
			
		/* Month without initial 0 */
		case "n":
			return this.getMonth() + 1;
		
		/* Number of days in the month */
		case "t":
			return daysInMonthes[this.getMonth()];
			
		/* The year is bissextile */	
		case "L" :
			return this.isLeapYear() ? 1 : 0;
			
		/* the year on 4 figures */
		case "Y" :
			return this.getFullYear();
		
		/* The year on 2 figures */
		case "y":
			return this.getFullYear().toString().substr(-2);
		
		/* Lowercase ante meridiem or post meiridem */
		case "a":
			return (this.getHours() < 12) ? "am" : "pm";
		
		/* Uppercase ante meridiem or post meiridem */
		case "A":
			return (this.getHours() < 12) ? "AM" : "PM";
		
		/* Swatch Internet time */
		case "B":
			return this.getMilliseconds();
			
		/* 12-hour format of an hour without leading zeros */
		case "g":
			return this.getHours() % 12;
		
		/* 24-hour format of an hour without leading zeros */
		case "G":
			return this.getHours();
			
		/* 12-hour format of an hour without leading zeros */
		case "h":
			var h = this.getHours() % 12;
			return h < 10 ? '0'+h : h;
		
		/* 24-hour format of an hour without leading zeros */
		case "H":
			var h = this.getHours();
			return h < 10 ? '0'+h : h;
		
		/* Minutes with leading 0 */
		case "i":
			var m = this.getMinutes();
			return m < 10 ? "0"+m : m;
		
		/* Seconds with leading zeros */
		case "s":
			var s=  this.getSeconds();
			return s < 10 ? "0"+s : s;

		default :
			return letter;
	}
};

Date.prototype.isLeapYear = function(){
	return (this.getFullYear() % 4 == 0 && this.getFullYear() % 100 != 0 || this.getFullYear() % 400 == 0);
};

Date.prototype.diff = function(todate){	
	var diff = this - todate;	
	var output = {};
	var divideBy = {		
		milliseconds : 1,
		seconds : 1000,
		minutes : 1000 * 60,
		hours : 1000 * 60 * 60,
		days : 1000 * 60 * 60 * 24,
		weeks : 1000 * 60 * 60 * 24 * 7,
	};	
	
	for(var i in divideBy){
		output[i] = Math.floor( diff/divideBy[i]);	
	}
	
	return output;	
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/