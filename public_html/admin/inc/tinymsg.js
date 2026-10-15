/*
    Members tinymsg Javascript for members - GuppY PHP Script -
      Web site = http://www.freeguppy.org/
      e-mail   = info@freeguppy.org

    Version History :
      v4.6.5 (05 December 2007)  : initial release (by Icare)
*/
  function AddSmiley1(zoop) {
  if (document.selection) {
    document.membmsg.msg1.focus();
    var sel = document.selection.createRange();
    sel.text = zoop;
	}
	else {
    document.membmsg.msg1.focus();
    document.membmsg.msg1.value=document.membmsg.msg1.value+zoop;
  }
    document.membmsg.msg1.focus();
 }
  function format1(f) {
  if (document.selection) {
    var str = document.selection.createRange().text;
    document.membmsg.msg1.focus();
    var sel = document.selection.createRange();
    sel.text = '[' + f + ']' + str + '[\/' + f + ']';
    document.membmsg.msg1.focus();
    return;
  }
  else if (navigator.product == "Gecko" && navigator.productSub >= 20030210) {
    var startPos = document.membmsg.msg1.selectionStart;
    var endPos = document.membmsg.msg1.selectionEnd;
    var chaine = document.membmsg.msg1.value;
    document.membmsg.msg1.value = chaine.substring(0, startPos) + '[' + f + ']' + chaine.substring(startPos, endPos) + '[\/' + f + ']' + chaine.substring(endPos, chaine.length);
    document.membmsg.msg1.focus();
    return;
  }
  else {
    var chaine = document.nmsg1r.msg1.value;
    document.membmsg.msg1.value = chaine + '[' + f + '] [\/' + f + ']';
    document.membmsg.msg1.focus();
    return;
	}
}
  function dolink1(f) {
  if (document.selection) {
     var str = document.selection.createRange().text;
     document.membmsg.msg1.focus();
     var sel = document.selection.createRange();
     sel.text = '[l]' + f + str + '[\/l]';
     document.membmsg.msg1.focus();
     return;
  }
  else if (navigator.product == "Gecko" && navigator.productSub >= 20030210) {
     var startPos = document.membmsg.msg1.selectionStart;
     var endPos = document.membmsg.msg1.selectionEnd;
     var chaine = document.membmsg.msg1.value;
     document.membmsg.msg1.value = chaine.substring(0, startPos) + '[l]' + f + chaine.substring(startPos, endPos) + '[\/l]' + chaine.substring(endPos, chaine.length);
     document.membmsg.msg1.focus();
     return;
  }
  else {
      var chaine = document.membmsg.msg1.value;
      document.membmsg.msg1.value = chaine + '[l]' + f + '[\/l]';
      document.membmsg.msg1.focus();
      return;
	}
}
  function AddSmiley2(zoop) {
  if (document.selection) {
    document.membmsg.msg2.focus();
    var sel = document.selection.createRange();
    sel.text = zoop;
	}
	else {
    document.membmsg.msg2.focus();
    document.membmsg.msg2.value=document.membmsg.msg2.value+zoop;
  }
    document.membmsg.msg2.focus();
 }
  function format2(f) {
  if (document.selection) {
    var str = document.selection.createRange().text;
    document.membmsg.msg2.focus();
    var sel = document.selection.createRange();
    sel.text = '[' + f + ']' + str + '[\/' + f + ']';
    document.membmsg.msg2.focus();
    return;
  }
  else if (navigator.product == "Gecko" && navigator.productSub >= 20030210) {
    var startPos = document.membmsg.msg2.selectionStart;
    var endPos = document.membmsg.msg2.selectionEnd;
    var chaine = document.membmsg.msg2.value;
    document.membmsg.msg2.value = chaine.substring(0, startPos) + '[' + f + ']' + chaine.substring(startPos, endPos) + '[\/' + f + ']' + chaine.substring(endPos, chaine.length);
    document.membmsg.msg2.focus();
    return;
  }
  else {
    var chaine = document.membmsg.msg2.value;
    document.membmsg.msg2.value = chaine + '[' + f + '] [\/' + f + ']';
    document.membmsg.msg2.focus();
    return;
	}
}
  function dolink2(f) {
  if (document.selection) {
     var str = document.selection.createRange().text;
     document.membmsg.msg2.focus();
     var sel = document.selection.createRange();
     sel.text = '[l]' + f + str + '[\/l]';
     document.membmsg.msg2.focus();
     return;
  }
  else if (navigator.product == "Gecko" && navigator.productSub >= 20030210) {
     var startPos = document.membmsg.msg2.selectionStart;
     var endPos = document.membmsg.msg2.selectionEnd;
     var chaine = document.membmsg.msg2.value;
     document.membmsg.msg2.value = chaine.substring(0, startPos) + '[l]' + f + chaine.substring(startPos, endPos) + '[\/l]' + chaine.substring(endPos, chaine.length);
     document.membmsg.msg2.focus();
     return;
  }
  else {
      var chaine = document.membmsg.msg2.value;
      document.membmsg.msg2.value = chaine + '[l]' + f + '[\/l]';
      document.membmsg.msg2.focus();
      return;
	}
}
