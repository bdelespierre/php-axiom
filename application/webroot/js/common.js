/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

String.prototype.rtrim = function (chars) {
	return this.replace(new RegExp("[" + (chars || '\\s') + "]+$", "g"), "");
};

String.rtrim = function (str, chars) {
	return (str + '').rtrim(chars);
};

String.prototype.ltrim = function (chars) {
	return this.replace(new RegExp("^[" + (chars || '\\s') + "]+", "g"), "");
};

String.ltrim = function (str, chars) {
	return (str + '').ltrim(chars);
};

String.prototype.trim = function (chars) {
	var c = chars || '\\s';
	return this.rtrim(c).ltrim(c);
};

String.trim = function (str, chars) {
	return (str + '').trim(chars);
};

String.prototype.pos = function (needle, offset) {
	var i = this.indexOf(needle, (offset || 0));
	return i === -1 ? false : i;
};

String.pos = function (str, needle, offset) {
	return (str + '').pos(needle, offset);
};

String.prototype.repeat = function ( multiplier ) {
	for (var output = []; multiplier > 0; output[--multiplier] = this) {/* do nothing */}
	return output.join('');
};

String.repeat = function (str, multiplier) {
	return (str + '').repeat(multiplier);
};

String.prototype.format = function () {
	if (arguments.length == 0)
		return this;
		
	var output = new String(this);
	for (var token = 0; token<arguments.length; token++)
		output = output.replace(new RegExp("\\{" + token + "\\}", "gi"), arguments[token]);
	
	return output;
};

String.format = function (str) {
	return String.prototype.format.apply((str + ''), Array.prototype.slice.call(arguments, 1));
};

Object.getType = function (obj) {
	return Object.prototype.toString().call(obj).slice(8, -1).toLowerCase();
};

Object.clone = function (obj) {
	return eval(uneval(obj));
};

Array.max = function (arr) {
	var max;
	for (var i in arr) {
		if (typeof(arr[i]) == 'function') {
			continue;
		}
		if (max == undefined) {
			max = arr[i];
			continue;
		}
		max = Math.max(arr[i], max);
	}
	return max;
};

Array.min = function (arr) {
	var min;
	for (var i in arr) {
		if (typeof(arr[i]) == 'function') {
			continue;
		}
		if (min == undefined) {
			min = arr[i];
			continue;
		}
		min = Math.min(arr[i], min);
	}
	return min;
};

Math.rand = function (min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min;
};