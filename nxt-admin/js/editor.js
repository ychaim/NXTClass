var switchEditors={switchto:function(b){var c=b.id,a=c.length,e=c.substr(0,a-5),d=c.substr(a-4);this.go(e,d)},go:function(g,f){g=g||"content";f=f||"toggle";var c=this,b=tinyMCE.get(g),a,d,e=tinymce.DOM;a="nxt-"+g+"-wrap";d=e.get(g);if("toggle"==f){if(b&&!b.isHidden()){f="html"}else{f="tmce"}}if("tmce"==f||"tinymce"==f){if(b&&!b.isHidden()){return false}if(typeof(QTags)!="undefined"){QTags.closeAllTags(g)}if(tinyMCEPreInit.mceInit[g]&&tinyMCEPreInit.mceInit[g].nxtautop){d.value=c.nxtautop(d.value)}if(b){b.show()}else{b=new tinymce.Editor(g,tinyMCEPreInit.mceInit[g]);b.render()}e.removeClass(a,"html-active");e.addClass(a,"tmce-active");setUserSetting("editor","tinymce")}else{if("html"==f){if(b&&b.isHidden()){return false}if(b){d.style.height=b.getContentAreaContainer().offsetHeight+20+"px";b.hide()}e.removeClass(a,"tmce-active");e.addClass(a,"html-active");setUserSetting("editor","html")}}return false},_nxt_Nop:function(b){var c,a;if(b.indexOf("<pre")!=-1||b.indexOf("<script")!=-1){b=b.replace(/<(pre|script)[^>]*>[\s\S]+?<\/\1>/g,function(d){d=d.replace(/<br ?\/?>(\r\n|\n)?/g,"<nxt_temp>");return d.replace(/<\/?p( [^>]*)?>(\r\n|\n)?/g,"<nxt_temp>")})}c="blockquote|ul|ol|li|table|thead|tbody|tfoot|tr|th|td|div|h[1-6]|p|fieldset";b=b.replace(new RegExp("\\s*</("+c+")>\\s*","g"),"</$1>\n");b=b.replace(new RegExp("\\s*<((?:"+c+")(?: [^>]*)?)>","g"),"\n<$1>");b=b.replace(/(<p [^>]+>.*?)<\/p>/g,"$1</p#>");b=b.replace(/<div( [^>]*)?>\s*<p>/gi,"<div$1>\n\n");b=b.replace(/\s*<p>/gi,"");b=b.replace(/\s*<\/p>\s*/gi,"\n\n");b=b.replace(/\n[\s\u00a0]+\n/g,"\n\n");b=b.replace(/\s*<br ?\/?>\s*/gi,"\n");b=b.replace(/\s*<div/g,"\n<div");b=b.replace(/<\/div>\s*/g,"</div>\n");b=b.replace(/\s*\[caption([^\[]+)\[\/caption\]\s*/gi,"\n\n[caption$1[/caption]\n\n");b=b.replace(/caption\]\n\n+\[caption/g,"caption]\n\n[caption");a="blockquote|ul|ol|li|table|thead|tbody|tfoot|tr|th|td|h[1-6]|pre|fieldset";b=b.replace(new RegExp("\\s*<((?:"+a+")(?: [^>]*)?)\\s*>","g"),"\n<$1>");b=b.replace(new RegExp("\\s*</("+a+")>\\s*","g"),"</$1>\n");b=b.replace(/<li([^>]*)>/g,"\t<li$1>");if(b.indexOf("<hr")!=-1){b=b.replace(/\s*<hr( [^>]*)?>\s*/g,"\n\n<hr$1>\n\n")}if(b.indexOf("<object")!=-1){b=b.replace(/<object[\s\S]+?<\/object>/g,function(d){return d.replace(/[\r\n]+/g,"")})}b=b.replace(/<\/p#>/g,"</p>\n");b=b.replace(/\s*(<p [^>]+>[\s\S]*?<\/p>)/g,"\n$1");b=b.replace(/^\s+/,"");b=b.replace(/[\s\u00a0]+$/,"");b=b.replace(/<nxt_temp>/g,"\n");return b},_nxt_Autop:function(a){var b="table|thead|tfoot|tbody|tr|td|th|caption|col|colgroup|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|address|math|p|h[1-6]|fieldset|legend|hr|noscript|menu|samp|header|footer|article|section|hgroup|nav|aside|details|summary";if(a.indexOf("<object")!=-1){a=a.replace(/<object[\s\S]+?<\/object>/g,function(c){return c.replace(/[\r\n]+/g,"")})}a=a.replace(/<[^<>]+>/g,function(c){return c.replace(/[\r\n]+/g," ")});if(a.indexOf("<pre")!=-1||a.indexOf("<script")!=-1){a=a.replace(/<(pre|script)[^>]*>[\s\S]+?<\/\1>/g,function(c){return c.replace(/(\r\n|\n)/g,"<nxt_temp_br>")})}a=a+"\n\n";a=a.replace(/<br \/>\s*<br \/>/gi,"\n\n");a=a.replace(new RegExp("(<(?:"+b+")(?: [^>]*)?>)","gi"),"\n$1");a=a.replace(new RegExp("(</(?:"+b+")>)","gi"),"$1\n\n");a=a.replace(/<hr( [^>]*)?>/gi,"<hr$1>\n\n");a=a.replace(/\r\n|\r/g,"\n");a=a.replace(/\n\s*\n+/g,"\n\n");a=a.replace(/([\s\S]+?)\n\n/g,"<p>$1</p>\n");a=a.replace(/<p>\s*?<\/p>/gi,"");a=a.replace(new RegExp("<p>\\s*(</?(?:"+b+")(?: [^>]*)?>)\\s*</p>","gi"),"$1");a=a.replace(/<p>(<li.+?)<\/p>/gi,"$1");a=a.replace(/<p>\s*<blockquote([^>]*)>/gi,"<blockquote$1><p>");a=a.replace(/<\/blockquote>\s*<\/p>/gi,"</p></blockquote>");a=a.replace(new RegExp("<p>\\s*(</?(?:"+b+")(?: [^>]*)?>)","gi"),"$1");a=a.replace(new RegExp("(</?(?:"+b+")(?: [^>]*)?>)\\s*</p>","gi"),"$1");a=a.replace(/\s*\n/gi,"<br />\n");a=a.replace(new RegExp("(</?(?:"+b+")[^>]*>)\\s*<br />","gi"),"$1");a=a.replace(/<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)/gi,"$1");a=a.replace(/(?:<p>|<br ?\/?>)*\s*\[caption([^\[]+)\[\/caption\]\s*(?:<\/p>|<br ?\/?>)*/gi,"[caption$1[/caption]");a=a.replace(/(<(?:div|th|td|form|fieldset|dd)[^>]*>)(.*?)<\/p>/g,function(e,d,f){if(f.match(/<p( [^>]*)?>/)){return e}return d+"<p>"+f+"</p>"});a=a.replace(/<nxt_temp_br>/g,"\n");return a},pre_nxtautop:function(b){var a=this,d={o:a,data:b,unfiltered:b},c=typeof(jQuery)!="undefined";if(c){jQuery("body").trigger("beforePrenxtautop",[d])}d.data=a._nxt_Nop(d.data);if(c){jQuery("body").trigger("afterPrenxtautop",[d])}return d.data},nxtautop:function(b){var a=this,d={o:a,data:b,unfiltered:b},c=typeof(jQuery)!="undefined";if(c){jQuery("body").trigger("beforenxtautop",[d])}d.data=a._nxt_Autop(d.data);if(c){jQuery("body").trigger("afternxtautop",[d])}return d.data}};