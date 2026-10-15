function msgdefil(){afftexte=texte.substring(postexte,texte.length)+texte.substring(0,postexte),postexte++,postexte==texte.length&&(postexte=0),window.status=afftexte,vitessedefil=setTimeout("msgdefil()",150)}
function WriteMailTo(a,b,c,d){var e=112,f=300,g=(screen.height-e)/2,h=(screen.width-f)/2;""==a&&(a=b+"@"+c+"."+d),fenetremail=window.open("","","top="+g+", left="+h+", width="+f+", height="+e+", directories=no, menubar=no, status=no, resizable=no, location=no"),fenetreNote=fenetremail,null!=fenetreNote&&(docmail=fenetremail.document,textemail='<html><head><title>email</title><meta http-equiv="Content-Type" content="text/html; charset="'+charset+'" ></head><body onblur="javascript:setTimeout(\'window.close();\',5000);" style="margin: 5px; background-color:<?php echo $forum[0]; ?>; color: <?php echo $texte[0] ?>; overflow: hidden;"><div align="center"><br /><?php echo $web173; ?> <b>'+a+'</b> :<br /><br /><a  href="mailto:'+b+"@"+c+"."+d+'">'+b+"@"+c+"."+d+"</a><br /><br /></div></body></html>",docmail.write(textemail),docmail.close())}
function ContactMe(a,b,c,d,e){var f=112,g=360,h=(screen.height-f)/2,i=(screen.width-g)/2;switch(""!=e&&(e="."+e),""!=d&&(d="@"+d),ircident=c+d+e,a){case"ICQ":ircsite="http://www.icq.com/people/webmsg.php?to="+ircident;break;case"MSN":ircsite="msnim:chat?contact="+ircident;break;case"Yahoo":ircsite="ymsgr:sendim?"+ircident;break;case"Gtalk":ircsite="http://mail.google.com/mail/";break;case"Skype":ircsite="skype:"+ircident+"?call";break;case"AIM":ircsite="aim:goim?screenname="+ircident;break;case"Teamspeak":ircsite="teamspeak://"+ircident}fenetremail=window.open("","","top="+h+", left="+i+", width="+g+", height="+f+", directories=no, menubar=no, status=no, resizable=no, location=no"),fenetreNote=fenetremail,null!=fenetreNote&&(docmail=fenetremail.document,textemail='<html><head><title>email</title><meta http-equiv="Content-Type" content="text/html; charset="'+charset+'" ></head><body onblur="javascript:setTimeout(\'window.close();\',5000);" style="margin: 5px; background-color:<?php echo $forum[0]; ?>; color: <?php echo $texte[0] ?>; overflow: hidden;"><div align="center"><br /><?php echo $web173; ?> <b> '+b+" via "+a+"</b> :<br /><br /><br />",textemail+='<a href="'+ircsite+'">'+ircident+"</a>",textemail+="</div></body></html>",docmail.write(textemail),docmail.close())}
function AddFavo(a,b){return void 0==a&&(a=site0),void 0==b&&(b=site3),window.sidebar?(window.sidebar.addPanel(a,b,""),!1):window.external?(window.external.AddFavorite(b,a),!1):!0}
function PopupWindow(a,b,c,d,e,f){var g=(screen.height-d)/2,h=(screen.width-c)/2;window.open(a,b,"top="+g+", left="+h+", width="+c+", height="+d+", directories=no, menubar=no, status=no, resizable="+e+", scrollbars="+f+", location=no")}
function hideLoadingPage(){document.getElementById?document.getElementById("hidepage").style.visibility="hidden":document.layers?document.hidepage.visibility="hidden":document.all.hidepage.style.visibility="hidden"}
function MontreCacheItems(a,b,c,d){
    document.getElementById&&"inline"==document.getElementById(a).style.display||document.all&&"inline"==document.all[a].style.display||document.layers&&"inline"==document.layers[a].display
      ?(cache(a),montre(b,"inline"),document.getElementById&&null!=document.getElementById(c)||document.all&&void 0!=document.all[c]||document.layers&&void 0!=document.layers[c]
        ?montre(c,"block"):montre(d,"block"))
        :(cache(b),montre(a,"inline"),
          cache(document.getElementById&&null!=document.getElementById(c)||document.all&&void 0!=document.all[c]||document.layers&&void 0!=document.layers[c]?c:d)
        )
}
function montre(a,b){
    "block" != b && "inline" != b && (b = "inline-block"),
    document.getElementById
      ?document.getElementById(a).style.display = b
      :document.all
        ?document.all[a].style.display = b
        :document.layers&&(document.layers[a].display = b)
}
function cache(a){
    document.getElementById
      ?document.getElementById(a).style.display="none"
      :document.all
        ?document.all[a].style.display="none"
        :document.layers&&(document.layers[a].display="none")
}
function ToggleValue(a){document.getElementById?document.getElementById(a).value="on"==document.getElementById(a).value?"":"on":document.all?document.all[a].value="on"==document.all[a].value?"":"on":document.layers&&(document.layers[a].value="on"==document.layers[a].value?"":"on")}
function submenu(a){if(document.all)for(i=1;i<=maxsub;i++)menu=document.all["sub"+i],menu.style.display=i!=a?"none":"block";else if(document.getElementById)for(i=1;i<=maxsub;i++)menu=document.getElementById("sub"+i),menu.style.display=i!=a?"none":"block"}
function ActiveMenu(a,b,c,d,e,f){
    DesactiveMenu(a,b,c,d,f),
    ActiveItem(a,b,e,f)
}
function DesactiveMenu(a,b,c,d,e){
    for(i=c;i<c+d;i++)
        document.getElementById(b+i)&&document.getElementById(a+i)&&(cache(b+i),montre(a+i,e))
}
function ActiveItem(a,b,c,d){
    cache(a+c),montre(b+c,d)
}
function DesactiveItem(a,b,c,d){cache(b+c),montre(a+c,d)}window!=top&&(top.location=window.location);var postexte=0;