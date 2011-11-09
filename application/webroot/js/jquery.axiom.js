/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

jQuery.axiom = {
	
	/**
	 * Debug flag
	 * @var Boolean
	 */
	debug: undefined,
	
	/**
	 * Site Base URL
	 * (for plateform relative URL generation)
	 * @var String
	 */
	baseUrl: undefined,
	
	/**
	 * Current lang
	 * @var String
	 */
	lang: undefined,
	
	/**
	 * Initialize axiom decorations
	 * @param Object params [optional]
	 * @returns {jQuery.axiom}
	 */
	init: function () {
		var p = $.extend({ baseUrl: '', debug: false, lang: $('html').attr('lang') || '' }, arguments[0] || {});
		this.setDebug(p.debug)
			.setBaseUrl(p.baseUrl)
			.setLang(p.lang);
		return this;
	},
	
	/**
	 * Set the debug flag
	 * @param Boolean debug
	 * @returns {jQuery.axiom}
	 */
	setDebug: function (debug) {
		this.debug = Boolean(debug);
		return this;
	},
	
	/**
	 * Set the base URL used for jQuery.axiom.src & jQuery.axiom.url
	 * @param String base_url [optional]
	 * @return {jQuery.axiom}
	 */
	setBaseUrl: function () {
		var url = String(arguments[0]).trim('/') || '';
		this.baseUrl = url;
		return this;
	},
	
	/**
	 * Set the lang used for jQuery.axiom.i18n, jQuery.axim.loadTranslation & jQuery.axiom.url
	 * @param String lang
	 * @returns {jQuery.axiom}
	 */
	setLang: function (lang) {
		this.lang = String(lang);
		return this;
	},
	
	/**
	 * Triggers a synchronous call to retrieve translations from the server.
	 * The params object may take lang, url or modules parameters.
	 * @param Object params [optional]
	 * @return {jQuery.axiom}
	 */
	loadTranslations: function () {
		var d = {
			lang: this.lang,
			url: 'ajax/translations',
			modules: []
		};
		
		$.extend(d, arguments[0] || {});
		$.ajax({
			url: this.formatUrl(d.url || 'ajax/translations', d.lang),
			data: { modules: d.modules},
			dataType: 'json',
			async: false,
			context: this,
			success: function (json) {
				this.translations = json;
				if (this.debug && typeof console !== 'undefined')
					console.log("Translations loaded");
			},
			failure: function () {
				if (typeof console !== 'undefined')
					console.log("Unable to load translations");
			}
		});
		
		return this;
	},
	
	/**
	 * Performs a translation
	 * @param key
	 * @param arg [multiple] [optional] the arugments to be replaced in the translated string
	 * @returns String
	 */
	i18n: function (key) {
		if (typeof this.translations == 'undefined')
			throw "Translations not loaded";
		
		if (typeof $.sprintf == 'undefined')
			throw "Sprintf plugin not loaded";
		
		if (this.debug && typeof console != 'undefined')
			console.log('i18n: ' + this.translations[key]);
		
		var args = Array.prototype.slice.call(arguments, 1),
			format = this.translations[key] || '<!-- UNDEFINED TRANSLATION -->';
		return $.sprintf.apply(undefined, [format].concat(args));
	},

	/**
	 * Format a ressource URL
	 * @param String src [optional]
	 * @returns String
	 */
	formatSrc: function (src) {
		var cleaned_src = src.replace(this.baseUrl, '').trim('/');
		return ('/{0}/{1}').format(this.baseUrl, cleaned_src);
	},
	
	/**
	 * Format an URL
	 * @param String url [optional]
	 * @param String lang [optional]
	 * @returns String
	 */
	formatUrl: function (url, lang) {
		if (typeof lang == 'undefined')
			var lang = this.lang;
		
		var cleaned_url = (url || '').replace(new RegExp('(' + this.baseUrl + '|' + lang +')', 'gi'), '').trim('/');
		return ('/{0}/{1}/{2}').format(this.baseUrl, lang, cleaned_url);
	}
	
};