(function($) {
var currentFormEl = false, fs = {add:'ajaxAdd',del:'ajaxDel',dim:'ajaxDim',process:'process',recolor:'recolor'}, nxtList;

nxtList = {
	settings: {
		url: nxtListL10n.url, type: 'POST',
		response: 'ajax-response',

		what: '',
		alt: 'alternate', altOffset: 0,
		addColor: null, delColor: null, dimAddColor: null, dimDelColor: null,

		confirm: null,
		addBefore: null, addAfter: null,
		delBefore: null, delAfter: null,
		dimBefore: null, dimAfter: null
	},

	nonce: function(e,s) {
		var url = nxtAjax.unserialize(e.attr('href'));
		return s.nonce || url._ajax_nonce || $('#' + s.element + ' input[name=_ajax_nonce]').val() || url._nxtnonce || $('#' + s.element + ' input[name=_nxtnonce]').val() || 0;
	},

	parseClass: function(e,t) {
		var c = [], cl;
		try {
			cl = $(e).attr('class') || '';
			cl = cl.match(new RegExp(t+':[\\S]+'));
			if ( cl ) { c = cl[0].split(':'); }
		} catch(r) {}
		return c;
	},

	pre: function(e,s,a) {
		var bg, r;
		s = $.extend( {}, this.nxtList.settings, {
			element: null,
			nonce: 0,
			target: e.get(0)
		}, s || {} );
		if ( $.isFunction( s.confirm ) ) {
			if ( 'add' != a ) {
				bg = $('#' + s.element).css('backgroundColor');
				$('#' + s.element).css('backgroundColor', '#FF9966');
			}
			r = s.confirm.call(this,e,s,a,bg);
			if ( 'add' != a ) { $('#' + s.element).css('backgroundColor', bg ); }
			if ( !r ) { return false; }
		}
		return s;
	},

	ajaxAdd: function( e, s ) {
		e = $(e);
		s = s || {};
		var list = this, cls = nxtList.parseClass(e,'add'), es, valid, formData;
		s = nxtList.pre.call( list, e, s, 'add' );

		s.element = cls[2] || e.attr( 'id' ) || s.element || null;
		if ( cls[3] ) { s.addColor = '#' + cls[3]; }
		else { s.addColor = s.addColor || '#FFFF33'; }

		if ( !s ) { return false; }

		if ( !e.is("[class^=add:" + list.id + ":]") ) { return !nxtList.add.call( list, e, s ); }

		if ( !s.element ) { return true; }

		s.action = 'add-' + s.what;

		s.nonce = nxtList.nonce(e,s);

		es = $('#' + s.element + ' :input').not('[name=_ajax_nonce], [name=_nxtnonce], [name=action]');
		valid = nxtAjax.validateForm( '#' + s.element );
		if ( !valid ) { return false; }

		s.data = $.param( $.extend( { _ajax_nonce: s.nonce, action: s.action }, nxtAjax.unserialize( cls[4] || '' ) ) );
		formData = $.isFunction(es.fieldSerialize) ? es.fieldSerialize() : es.serialize();
		if ( formData ) { s.data += '&' + formData; }

		if ( $.isFunction(s.addBefore) ) {
			s = s.addBefore( s );
			if ( !s ) { return true; }
		}
		if ( !s.data.match(/_ajax_nonce=[a-f0-9]+/) ) { return true; }

		s.success = function(r) {
			var res = nxtAjax.parseAjaxResponse(r, s.response, s.element), o;
			if ( !res || res.errors ) { return false; }

			if ( true === res ) { return true; }

			jQuery.each( res.responses, function() {
				nxtList.add.call( list, this.data, $.extend( {}, s, { // this.firstChild.nodevalue
					pos: this.position || 0,
					id: this.id || 0,
					oldId: this.oldId || null
				} ) );
			} );

			if ( $.isFunction(s.addAfter) ) {
				o = this.complete;
				this.complete = function(x,st) {
					var _s = $.extend( { xml: x, status: st, parsed: res }, s );
					s.addAfter( r, _s );
					if ( $.isFunction(o) ) { o(x,st); }
				};
			}
			list.nxtList.recolor();
			nxtList.clear.call(list,'#' + s.element);
		};

		$.ajax( s );
		return false;
	},

	ajaxDel: function( e, s ) {
		e = $(e); s = s || {};
		var list = this, cls = nxtList.parseClass(e,'delete'), element, anim;
		s = nxtList.pre.call( list, e, s, 'delete' );

		s.element = cls[2] || s.element || null;
		if ( cls[3] ) { s.delColor = '#' + cls[3]; }
		else { s.delColor = s.delColor || '#FF3333'; }

		if ( !s || !s.element ) { return false; }

		s.action = 'delete-' + s.what;

		s.nonce = nxtList.nonce(e,s);

		s.data = $.extend(
			{ action: s.action, id: s.element.split('-').pop(), _ajax_nonce: s.nonce },
			nxtAjax.unserialize( cls[4] || '' )
		);

		if ( $.isFunction(s.delBefore) ) {
			s = s.delBefore( s );
			if ( !s ) { return true; }
		}
		if ( !s.data._ajax_nonce ) { return true; }

		element = $('#' + s.element);

		if ( 'none' != s.delColor ) {
			anim = 'slideUp';
			if ( element.css( 'display' ).match(/table/) )
				anim = 'fadeOut'; // Can't slideup table rows and other table elements.  Known jQuery bug
			element
				.animate( { backgroundColor: s.delColor }, 'fast'  )[anim]( 'fast' )
				.queue( function() { list.nxtList.recolor(); $(this).dequeue(); } );
		} else {
			list.nxtList.recolor();
		}

		s.success = function(r) {
			var res = nxtAjax.parseAjaxResponse(r, s.response, s.element), o;
			if ( !res || res.errors ) {
				element.stop().stop().css( 'backgroundColor', '#FF3333' ).show().queue( function() { list.nxtList.recolor(); $(this).dequeue(); } );
				return false;
			}
			if ( $.isFunction(s.delAfter) ) {
				o = this.complete;
				this.complete = function(x,st) {
					element.queue( function() {
						var _s = $.extend( { xml: x, status: st, parsed: res }, s );
						s.delAfter( r, _s );
						if ( $.isFunction(o) ) { o(x,st); }
					} ).dequeue();
				};
			}
		};
		$.ajax( s );
		return false;
	},

	ajaxDim: function( e, s ) {
		if ( $(e).parent().css('display') == 'none' ) // Prevent hidden links from being clicked by hotkeys
			return false;
		e = $(e); s = s || {};
		var list = this, cls = nxtList.parseClass(e,'dim'), element, isClass, color, dimColor;
		s = nxtList.pre.call( list, e, s, 'dim' );

		s.element = cls[2] || s.element || null;
		s.dimClass =  cls[3] || s.dimClass || null;
		if ( cls[4] ) { s.dimAddColor = '#' + cls[4]; }
		else { s.dimAddColor = s.dimAddColor || '#FFFF33'; }
		if ( cls[5] ) { s.dimDelColor = '#' + cls[5]; }
		else { s.dimDelColor = s.dimDelColor || '#FF3333'; }

		if ( !s || !s.element || !s.dimClass ) { return true; }

		s.action = 'dim-' + s.what;

		s.nonce = nxtList.nonce(e,s);

		s.data = $.extend(
			{ action: s.action, id: s.element.split('-').pop(), dimClass: s.dimClass, _ajax_nonce : s.nonce },
			nxtAjax.unserialize( cls[6] || '' )
		);

		if ( $.isFunction(s.dimBefore) ) {
			s = s.dimBefore( s );
			if ( !s ) { return true; }
		}

		element = $('#' + s.element);
		isClass = element.toggleClass(s.dimClass).is('.' + s.dimClass);
		color = nxtList.getColor( element );
		element.toggleClass( s.dimClass )
		dimColor = isClass ? s.dimAddColor : s.dimDelColor;
		if ( 'none' != dimColor ) {
			element
				.animate( { backgroundColor: dimColor }, 'fast' )
				.queue( function() { element.toggleClass(s.dimClass); $(this).dequeue(); } )
				.animate( { backgroundColor: color }, { complete: function() { $(this).css( 'backgroundColor', '' ); } } );
		}

		if ( !s.data._ajax_nonce ) { return true; }

		s.success = function(r) {
			var res = nxtAjax.parseAjaxResponse(r, s.response, s.element), o;
			if ( !res || res.errors ) {
				element.stop().stop().css( 'backgroundColor', '#FF3333' )[isClass?'removeClass':'addClass'](s.dimClass).show().queue( function() { list.nxtList.recolor(); $(this).dequeue(); } );
				return false;
			}
			if ( $.isFunction(s.dimAfter) ) {
				o = this.complete;
				this.complete = function(x,st) {
					element.queue( function() {
						var _s = $.extend( { xml: x, status: st, parsed: res }, s );
						s.dimAfter( r, _s );
						if ( $.isFunction(o) ) { o(x,st); }
					} ).dequeue();
				};
			}
		};

		$.ajax( s );
		return false;
	},

	// From jquery.color.js: jQuery Color Animation by John Resig
	getColor: function( el ) {
		if ( el.constructor == Object )
			el = el.get(0);
		var elem = el, color, rgbaTrans = new RegExp( "rgba\\(\\s*0,\\s*0,\\s*0,\\s*0\\s*\\)", "i" );
		do {
			color = jQuery.curCSS(elem, 'backgroundColor');
			if ( color != '' && color != 'transparent' && !color.match(rgbaTrans) || jQuery.nodeName(elem, "body") )
				break;
		} while ( elem = elem.parentNode );
		return color || '#ffffff';
	},

	add: function( e, s ) {
		e = $(e);

		var list = $(this), old = false, _s = { pos: 0, id: 0, oldId: null }, ba, ref, color;
		if ( 'string' == typeof s ) { s = { what: s }; }
		s = $.extend(_s, this.nxtList.settings, s);
		if ( !e.size() || !s.what ) { return false; }
		if ( s.oldId ) { old = $('#' + s.what + '-' + s.oldId); }
		if ( s.id && ( s.id != s.oldId || !old || !old.size() ) ) { $('#' + s.what + '-' + s.id).remove(); }

		if ( old && old.size() ) {
			old.replaceWith(e);
		} else if ( isNaN(s.pos) ) {
			ba = 'after';
			if ( '-' == s.pos.substr(0,1) ) {
				s.pos = s.pos.substr(1);
				ba = 'before';
			}
			ref = list.find( '#' + s.pos );
			if ( 1 === ref.size() ) { ref[ba](e); }
			else { list.append(e); }
		} else if ( s.pos < 0 ) {
			list.prepend(e);
		} else {
			list.append(e);
		}

		if ( s.alt ) {
			if ( ( list.children(':visible').index( e[0] ) + s.altOffset ) % 2 ) { e.removeClass( s.alt ); }
			else { e.addClass( s.alt ); }
		}

		if ( 'none' != s.addColor ) {
			color = nxtList.getColor( e );
			e.css( 'backgroundColor', s.addColor ).animate( { backgroundColor: color }, { complete: function() { $(this).css( 'backgroundColor', '' ); } } );
		}
		list.each( function() { this.nxtList.process( e ); } );
		return e;
	},

	clear: function(e) {
		var list = this, t, tag;
		e = $(e);
		if ( list.nxtList && e.parents( '#' + list.id ).size() ) { return; }
		e.find(':input').each( function() {
			if ( $(this).parents('.form-no-clear').size() )
				return;
			t = this.type.toLowerCase();
			tag = this.tagName.toLowerCase();
			if ( 'text' == t || 'password' == t || 'textarea' == tag ) { this.value = ''; }
			else if ( 'checkbox' == t || 'radio' == t ) { this.checked = false; }
			else if ( 'select' == tag ) { this.selectedIndex = null; }
		});
	},

	process: function(el) {
		var list = this;
		$("[class^=add:" + list.id + ":]", el || null)
			.filter('form').submit( function() { return list.nxtList.add(this); } ).end()
			.not('form').click( function() { return list.nxtList.add(this); } ).each( function() {
				var addEl = this, c = nxtList.parseClass(this,'add')[2] || addEl.id, forms = [], ins = [];
				if ( !c ) { return; }
				// this is all really inefficient
				$('#' + c + ' :input').focus( function() { currentFormEl = this; } ).blur( function() { currentFormEl = false; } ).each( function() {
					ins.push(this);
					var f = $(this).parents('form:first').get(0);
					if ( $.inArray(f,forms) < 0 ) { forms.push(f); }
				} );
				$(forms).submit( function() {
					if ( 0 <= $.inArray(currentFormEl,ins) ) {
						$(addEl).trigger( 'click' );
						$(currentFormEl).focus();
						return false;
					}
				} );
			} );
		$("[class^=delete:" + list.id + ":]", el || null).click( function() { return list.nxtList.del(this); } );
		$("[class^=dim:" + list.id + ":]", el || null).click( function() { return list.nxtList.dim(this); } );
	},

	recolor: function() {
		var list = this, items, eo;
		if ( !list.nxtList.settings.alt ) { return; }
		items = $('.list-item:visible', list);
		if ( !items.size() ) { items = $(list).children(':visible'); }
		eo = [':even',':odd'];
		if ( list.nxtList.settings.altOffset % 2 ) { eo.reverse(); }
		items.filter(eo[0]).addClass(list.nxtList.settings.alt).end().filter(eo[1]).removeClass(list.nxtList.settings.alt);
	},

	init: function() {
		var lists = this;
		lists.nxtList.process = function(a) {
			lists.each( function() {
				this.nxtList.process(a);
			} );
		};
		lists.nxtList.recolor = function() {
			lists.each( function() {
				this.nxtList.recolor();
			} );
		};
	}
};

$.fn.nxtList = function( settings ) {
	this.each( function() {
		var _this = this;
		this.nxtList = { settings: $.extend( {}, nxtList.settings, { what: nxtList.parseClass(this,'list')[1] || '' }, settings ) };
		$.each( fs, function(i,f) { _this.nxtList[i] = function( e, s ) { return nxtList[f].call( _this, e, s ); }; } );
	} );
	nxtList.init.call(this);
	this.nxtList.process();
	return this;
};

})(jQuery);
