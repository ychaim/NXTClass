(function(a){nxtWordCount={settings:{strip:/<[a-zA-Z\/][^<>]*>/g,clean:/[0-9.(),;:!?%#$¿'"_+=\\/-]+/g,count:/\S\s+/g},block:0,wc:function(d){var e=this,c=a(".word-count"),b=0;if(e.block){return}e.block=1;setTimeout(function(){if(d){d=d.replace(e.settings.strip," ").replace(/&nbsp;|&#160;/gi," ");d=d.replace(e.settings.clean,"");d.replace(e.settings.count,function(){b++})}c.html(b.toString());setTimeout(function(){e.block=0},2000)},1)}};a(document).bind("nxtcountwords",function(c,b){nxtWordCount.wc(b)})}(jQuery));