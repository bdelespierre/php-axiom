/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Right trim a string by the given characters
 * @param Srting chars [Optionnal]
 * @returns String
 */
String.prototype.rtrim = function (chars) {
	return this.replace(new RegExp("[" + (chars || '\\s') + "]+$", "g"), "");
};

String.rtrim = function (str, chars) {
	return (str + '').rtrim(chars);
};

/**
 * Left trim a string by the given characters
 * @param String chars [Optionnal]
 * @returns String
 */
String.prototype.ltrim = function (chars) {
	return this.replace(new RegExp("^[" + (chars || '\\s') + "]+", "g"), "");
};

String.ltrim = function (str, chars) {
	return (str + '').ltrim(chars);
};

/**
 * Trim a string by the given characters
 * @param String chars [Optionnal]
 * @returns String
 */
String.prototype.trim = function (chars) {
	var c = chars || '\\s';
	return this.rtrim(c).ltrim(c);
};

String.trim = function (str, chars) {
	return (str + '').trim(chars);
};

/**
 * Get position of the needle begining at the specified offset.
 * Will return false if the needle could not be found.
 * @note This method may return fasle but may also return a false equivalent value
 * @param String needle
 * @param Integer offset [optionnal]
 * @returns Integer
 */
String.prototype.pos = function (needle, offset) {
	var i = this.indexOf(needle, (offset || 0));
	return i === -1 ? false : i;
};

String.pos = function (str, needle, offset) {
	return (str + '').pos(needle, offset);
};

/**
 * Repeat a string by the given multiplier
 * @param Interger multiplier
 * @returns String
 */
String.prototype.repeat = function ( multiplier ) {
	for (var output = []; multiplier > 0; output[--multiplier] = this) {/* do nothing */}
	return output.join('');
};

String.repeat = function (str, multiplier) {
	return (str + '').repeat(multiplier);
};

/**
 * Format a pattern by replacing placeholder by arguments values.
 * @example ('{0} {1}').format('hello', 'world') would produce "hello world"
 * @returns String
 */
String.prototype.format = function () {
	if (arguments.length == 0)
		return this;
		
	var output = this.clone();
	for (var token = 0; token<arguments.length; token++)
		output = output.replace(new RegExp("\\{" + token + "\\}", "gi"), arguments[token]);
	
	return output;
};

String.format = function (str) {
	return String.prototype.format.apply((str + ''), Array.prototype.slice.call(arguments, 1));
};

/**
 * Get the real type of an object
 * @example (new Array).getType() will return 'arrray'
 * @returns String
 */
Object.prototype.getType = function () {
	return Object.prototype.toString.call(this).slice(8, -1).toLowerCase();
};

Object.getType = function (obj) {
	return obj.getType();
};

/**
 * Clone an object
 * @returns Object
 */
Object.prototype.clone = function () {
  return eval(uneval(this));
};

Object.clone = function (obj) {
	return obj.clone();
};

/**
 * Apply a lambda function to each item (not function) of an object
 * @example ({a:1,b:2,c:3}).each(function (i,item) { console.log(i, item); });
 * @param Function func
 * @returns {Object}
 */
//Object.prototype.each = function (func) {
//	for (member in this) {
//		if (this[member].getType() == 'function')
//			continue;
//		
//		func.apply(this, [member, this[member]]);
//	}
//	return this;
//};
//
//Object.each = function (obj, func) {
//	return obj.each(func);
//};

/**
 * Sugar by Douglas Crockford
 * @author Douglas Crockford
 * @see http://www.crockford.com/javascript/inheritance.html#sugar
 */
Function.prototype.method = function (name, func) {
    this.prototype[name] = func;
    return this;
};

Function.method('inherits', function (parent) {
    var d = {}, p = (this.prototype = new parent());
    this.method('uber', function uber(name) {
        if (!(name in d)) {
            d[name] = 0;
        }        
        var f, r, t = d[name], v = parent.prototype;
        if (t) {
            while (t) {
                v = v.constructor.prototype;
                t -= 1;
            }
            f = v[name];
        } else {
            f = p[name];
            if (f == this[name]) {
                f = v[name];
            }
        }
        d[name] += 1;
        r = f.apply(this, Array.prototype.slice.apply(arguments, [1]));
        d[name] -= 1;
        return r;
    });
    return this;
});

Function.method('swiss', function (parent) {
    for (var i = 1; i < arguments.length; i += 1) {
        var name = arguments[i];
        this.prototype[name] = parent.prototype[name];
    }
    return this;
});
