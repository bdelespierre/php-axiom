/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Admin JS
 * @require jQuery 1.7
 * @require jQuery TableSorter
 * @require jQuery TipTip
 * @require jQuery UI 1.8
 * @require jQuery FacyBox
 * @require jQuery clEditor
 */
$(function () {
	
	// Decoration
	$('th:last').addClass('ui-corner-tr');
	$('th:first').addClass('ui-corner-tl');
	$('tfoot td:last').addClass('ui-corner-br');
	$('tfoot td:first').addClass('ui-corner-bl');

	$("form").addClass('ui-widget');
	$("fieldset").addClass('ui-widget-content ui-corner-all');
	$("legend").addClass('ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix');
	$("fieldset > div > :input").focus(function () { $(this).parents('.ui-corner-all').first().addClass('ui-state-active', 500); 
	}).blur(function () { $(this).parents('.ui-corner-all').first().removeClass('ui-state-active', 500);
	}).wrap($('<div class="ui-corner-all" />'));
	$("fieldset > div > span").buttonset();
	$("input[type='submit'],input[type='reset'],input[type='button'],button,.button").button();
	$("input[type='password']").val('');
	
    // Plugins
	$('.warning,.error').click(function () { $(this).hide('blind'); });
	$('.tiptip').tipTip({defaultPosition: 'top'});
	$('.date,input[name="date"]').datepicker({ dateFormat: 'yy-mm-dd' });
	$(".cledit").cleditor({width: '100%'});
	
	$("table.sortable").tablesorter({
		cssHeader: 'ui-widget-header',
		cssAsc: 'ui-state-active',
		cssDesc: 'ui-state-active',
		cancelSelection: true,
		widthFixed: true
	});
	
	$('a[rel=box]').fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600,
		'speedOut'		:	200,
		'overlayShow'	:	false
	});

    // Behavior
	$('#panel span').hover(
		function () { $(this).addClass('ui-state-active'); },
		function () { $(this).removeClass('ui-state-active'); }
	);

	$("tbody tr").hover(
		function () { $(this).addClass('ui-state-active'); },
		function () { $(this).removeClass('ui-state-active'); }
	);

	$(".confirm").click(function () {
		return confirm(axiom.i18n('admin.confirm_action'));
	});

	$(".foldable .title").prepend('<div class="ui-icon ui-icon-minus"></div>').css('cursor', 'pointer').click(function () {
		$(this).siblings().toggle('fast');
		$(this).find('div.ui-icon').toggleClass('ui-icon-plus ui-icon-minus');
	});
});
