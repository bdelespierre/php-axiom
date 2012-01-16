/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Axiom JS
 * @require jQuery
 * @require common.js
 */
var axiom = {
		lang: $('html').attr('lang') || 'en',
		base: $('base').attr('href') || '/',
		i18n: function (key) {
			if (this.translations === undefined)
				throw "Translations not loaded";
			
			if ($.sprintf === undefined)
				throw "jQuery Sprintf plugin not loaded";
			
			console.log( this.translations );
			
			var args = Array.prototype.slice.call(arguments, 1),
				format = this.translations[key] || '<!-- UNDEFINED TRANSLATION -->';
			
			return $.sprintf.apply(undefined, [format].concat(args));
		},
		url: function (url, lang) {
			var cleaned_url = (url || '').replace(new RegExp('({'+this.base+'}|{'+(lang||this.lang)+'})', 'gi'), '')/*.trim('/')*/;
			return ('{0}/{1}/{2}').format(this.base.rtrim('/'), lang||this.lang, cleaned_url);
		},
		src: function (src) {
			var cleaned_src = src.replace(this.base, '').trim('/');
			return ('{0}/{1}').format(this.base, cleaned_src);
		},
		load: function (callback,options) {
			var d = {
				lang: this.lang,
				url: 'ajax/translations',
				modules: []
			};
			
			$.extend(d, options || {});
			$.ajax({
				url: this.url(d.url, d.lang),
				data: { modules: d.modules },
				dataType: 'json',
				context: this,
				success: function (json) {
					if (!json.success)
						throw "Loading translations failed";
					
					this.lang = d.lang;
					this.translations = json.content;
					
					if (callback) callback(this);
				},
				failure: function () {
					throw "Unable to load translations from " + d.url;
				}
			});
			
			return this;
		}
};
